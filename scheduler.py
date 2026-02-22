import sys
import json
import os
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
    requirements = data.get('requirements', [])

    days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu']
    periods = [1, 2, 3, 4, 5, 6, 7] # 7 حصص يومياً كحد أقصى

    model = cp_model.CpModel()
    schedule = {}

    # 1. إنشاء المتغيرات
    for req in requirements:
        c, s, t = req['class'], req['subject'], req['teacher']
        for d in days:
            for p in periods:
                schedule[(c, s, t, d, p)] = model.NewBoolVar(f'sch_{c}_{s}_{t}_{d}_{p}')

    # ==========================================
    # 🔴 الخطوط الحمراء (Hard Constraints) لا يمكن كسرها
    # ==========================================
    
    # 1. الأستاذ لا يعطي أكثر من محاضرة في نفس الوقت
    teachers = list(set(r['teacher'] for r in requirements))
    for t in teachers:
        for d in days:
            for p in periods:
                model.AddAtMostOne(schedule[(r['class'], r['subject'], t, d, p)] for r in requirements if r['teacher'] == t)

    # 2. الفصل لا يأخذ أكثر من محاضرة في نفس الوقت
    classes = list(set(r['class'] for r in requirements))
    for c in classes:
        for d in days:
            for p in periods:
                model.AddAtMostOne(schedule[(c, r['subject'], r['teacher'], d, p)] for r in requirements if r['class'] == c)

    # 3. يجب إكمال نصاب الحصص كاملاً (لا نترك حصص ناقصة)
    for req in requirements:
        c, s, t, sessions = req['class'], req['subject'], req['teacher'], req['sessions']
        model.Add(sum(schedule[(c, s, t, d, p)] for d in days for p in periods) == sessions)


    # ==========================================
    # 🟢 القيود المرنة (Soft Constraints) والمكافآت والعقوبات
    # ==========================================
    objective_terms = []
    unwanted_dict = {str(t['name']): t.get('unwanted_slots', {}) for t in teachers_data}

    # أ. معالجة تفضيلات الأساتذة (العقاب والمكافأة)
    for req in requirements:
        c, s, t = req['class'], req['subject'], req['teacher']
        unwanted = unwanted_dict.get(t, {})
        for d in days:
            blocked_periods = unwanted.get(d, [])
            for p in periods:
                if p in blocked_periods:
                    # عقاب شديد جداً إذا كسرنا التفضيل
                    objective_terms.append(-1000 * schedule[(c, s, t, d, p)])
                else:
                    # مكافأة عادية إذا وضعنا الحصة في وقت متاح
                    objective_terms.append(10 * schedule[(c, s, t, d, p)])

    # ب. معالجة تقارب المحاضرات (محاضرات ورا بعض أو فرق حصة)
    working = {}
    for t in teachers:
        for d in days:
            for p in periods:
                working[(t, d, p)] = model.NewBoolVar(f'work_{t}_{d}_{p}')
                # هل الأستاذ يعمل في هذه الحصة؟
                model.Add(working[(t, d, p)] == sum(schedule[(r['class'], r['subject'], t, d, p)] for r in requirements if r['teacher'] == t))

    for t in teachers:
        for d in days:
            # مكافأة (الحصص المتتالية) ورا بعض
            for i in range(len(periods) - 1):
                p1, p2 = periods[i], periods[i+1]
                consec = model.NewBoolVar(f'consec_{t}_{d}_{p1}')
                model.AddBoolAnd([working[(t, d, p1)], working[(t, d, p2)]]).OnlyEnforceIf(consec)
                model.AddBoolOr([working[(t, d, p1)].Not(), working[(t, d, p2)].Not()]).OnlyEnforceIf(consec.Not())
                objective_terms.append(50 * consec) # +50 نقطة

            # مكافأة (فرق حصة واحدة فقط)
            for i in range(len(periods) - 2):
                p1, p2, p3 = periods[i], periods[i+1], periods[i+2]
                onegap = model.NewBoolVar(f'onegap_{t}_{d}_{p1}')
                model.AddBoolAnd([working[(t, d, p1)], working[(t, d, p2)].Not(), working[(t, d, p3)]]).OnlyEnforceIf(onegap)
                model.AddBoolOr([working[(t, d, p1)].Not(), working[(t, d, p2)], working[(t, d, p3)].Not()]).OnlyEnforceIf(onegap.Not())
                objective_terms.append(20 * onegap) # +20 نقطة

    # نأمر الخوارزمية بجمع أكبر عدد ممكن من النقاط
    model.Maximize(sum(objective_terms))

    # ==========================================
    # ⚙️ تشغيل الخوارزمية (البحث عن أفضل حل)
    # ==========================================
    solver = cp_model.CpSolver()
    # أعطينا الخوارزمية 20 ثانية كحد أقصى للبحث عن "أفضل جدول متاح"
    solver.parameters.max_time_in_seconds = 20.0 
    
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
            json.dump({"error": "مستحيل تماماً: عدد الحصص المطلوبة أكبر بكثير من سعة الأيام."}, f, ensure_ascii=False)

if __name__ == '__main__':
    main()