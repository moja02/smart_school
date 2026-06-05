import sys
import json
import os
from collections import defaultdict
from ortools.sat.python import cp_model

def main():
    base_path = os.path.dirname(os.path.abspath(__file__))
    json_path = os.path.join(base_path, 'constraints.json')

    try:
        with open(json_path, 'r', encoding='utf-8') as f:
            data = json.load(f)
    except Exception as e:
        print(f"Error reading JSON: {e}")
        sys.exit(1)

    teachers_data = data.get('teachers', [])
    raw_requirements = data.get('requirements', [])

    # ==========================================
    # تجميع الحصص لتجنب فقدان العدد المطلوب
    # ==========================================
    aggregated_reqs = defaultdict(int)
    for req in raw_requirements:
        key = (str(req['class']), str(req['subject']), str(req['teacher']))
        aggregated_reqs[key] += int(req['sessions'])

    requirements = [{'class': k[0], 'subject': k[1], 'teacher': k[2], 'sessions': v} for k, v in aggregated_reqs.items()]

    days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu']
    periods = [1, 2, 3, 4, 5, 6]

    model = cp_model.CpModel()
    schedule = {}

    # إنشاء المتغيرات
    for req in requirements:
        c, s, t = req['class'], req['subject'], req['teacher']
        for d in days:
            for p in periods:
                schedule[(c, s, t, d, p)] = model.NewBoolVar(f'sch_{c}_{s}_{t}_{d}_{p}')

    # ==========================================
    # 🔴 الخطوط الحمراء (Hard Constraints) - لا يمكن كسرها أبداً
    # ==========================================
    
    all_teachers = list(set(r['teacher'] for r in requirements))
    all_classes = list(set(r['class'] for r in requirements))

    # 1. الأستاذ لا يعطي أكثر من محاضرة واحدة في نفس الوقت
    for t in all_teachers:
        reqs_for_t = [r for r in requirements if r['teacher'] == t]
        for d in days:
            for p in periods:
                model.Add(sum(schedule[(r['class'], r['subject'], t, d, p)] for r in reqs_for_t) <= 1)

    # 2. الفصل لا يأخذ أكثر من محاضرة واحدة في نفس الوقت
    for c in all_classes:
        reqs_for_c = [r for r in requirements if r['class'] == c]
        for d in days:
            for p in periods:
                model.Add(sum(schedule[(c, r['subject'], r['teacher'], d, p)] for r in reqs_for_c) <= 1)

    # 3. الالتزام المطلق والصارم بعدد حصص المادة في الأسبوع
    for req in requirements:
        c, s, t, sessions = req['class'], req['subject'], req['teacher'], req['sessions']
        model.Add(sum(schedule[(c, s, t, d, p)] for d in days for p in periods) == sessions)

    # 4. منع تكرار نفس المادة لنفس الفصل في نفس اليوم (توزيع عادل)
    for req in requirements:
        c, s, t, sessions = req['class'], req['subject'], req['teacher'], req['sessions']
        for d in days:
            if sessions <= len(days):
                # إذا كانت المادة 5 حصص أو أقل في الأسبوع -> حصة واحدة فقط في اليوم
                model.Add(sum(schedule[(c, s, t, d, p)] for p in periods) <= 1)
            else:
                # إذا كانت مكثفة (أكثر من 5)، مسموح بحصتين كحد أقصى في اليوم
                max_per_day = (sessions + len(days) - 1) // len(days)
                model.Add(sum(schedule[(c, s, t, d, p)] for p in periods) <= max_per_day)

    # ==========================================
    # 🟢 القيود المرنة (Soft Constraints) - تفضيلات قابلة للكسر للضرورة القصوى
    # ==========================================
    objective_terms = []
    
    # 🛠️ الإصلاح الجذري لمشكلة الـ List القادمة من PHP
    unwanted_dict = {}
    for t in teachers_data:
        slots = t.get('unwanted_slots', {})
        if isinstance(slots, list): # إذا تم إرسالها من لارافيل كمصفوفة فارغة []
            slots = {}              # حوّلها لقاموس فارغ {} لتجنب الخطأ
        unwanted_dict[str(t['name'])] = slots

    # أ. معالجة التفضيلات كقيود مرنة شديدة العقاب
    for req in requirements:
        c, s, t = req['class'], req['subject'], req['teacher']
        unwanted = unwanted_dict.get(t, {})
        for d in days:
            blocked_periods = [int(x) for x in unwanted.get(d, [])]
            for p in periods:
                if p in blocked_periods:
                    # ⚠️ الخوارزمية ستهرب من وضع الحصة هنا (-10,000 نقطة عقاب)
                    objective_terms.append(-10000 * schedule[(c, s, t, d, p)])
                else:
                    # مكافأة عادية إذا وضع الحصة في وقت مباح
                    objective_terms.append(10 * schedule[(c, s, t, d, p)])

    # ب. مكافأة تجميع الحصص للمعلم لكي لا ينتظر طويلاً بين الحصص
    working = {}
    for t in all_teachers:
        reqs_for_t = [r for r in requirements if r['teacher'] == t]
        for d in days:
            for p in periods:
                working[(t, d, p)] = sum(schedule[(r['class'], r['subject'], t, d, p)] for r in reqs_for_t)

    for t in all_teachers:
        for d in days:
            for i in range(len(periods) - 1):
                p1, p2 = periods[i], periods[i+1]
                consec = model.NewBoolVar(f'consec_{t}_{d}_{p1}')
                model.Add(consec <= working[(t, d, p1)])
                model.Add(consec <= working[(t, d, p2)])
                objective_terms.append(50 * consec) # مكافأة كبيرة إذا كانت الحصص متتالية وراء بعضها

    # نأمر الخوارزمية بجمع أكبر عدد ممكن من النقاط
    model.Maximize(sum(objective_terms))

    # ==========================================
    # ⚙️ تشغيل الخوارزمية
    # ==========================================
    solver = cp_model.CpSolver()
    solver.parameters.max_time_in_seconds = 25.0 
    
    status = solver.Solve(model)

    if status == cp_model.OPTIMAL or status == cp_model.FEASIBLE:
        output = {'schedule': []}
        for req in requirements:
            c, s, t = req['class'], req['subject'], req['teacher']
            for d in days:
                for p in periods:
                    if solver.Value(schedule[(c, s, t, d, p)]) == 1:
                        output['schedule'].append({
                            'class': c,
                            'subject': s,
                            'teacher': t,
                            'day': d,
                            'slot': p
                        })
        with open(json_path, 'w', encoding='utf-8') as f:
            json.dump(output, f, indent=4, ensure_ascii=False)
    else:
        with open(json_path, 'w', encoding='utf-8') as f:
            json.dump({
                "error": "فشل إنشاء الجدول. إجمالي عدد الحصص المطلوبة يتجاوز عدد حصص الأسبوع المتاحة في المدرسة، لا يوجد مساحة فارغة لإكمال الجدول."
            }, f, ensure_ascii=False)

if __name__ == '__main__':
    main()