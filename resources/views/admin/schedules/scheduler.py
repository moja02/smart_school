import sys
import json
import os
import math
from ortools.sat.python import cp_model

def main():
    # 1. تحديد مسارات الملفات
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

    if not requirements:
        with open(json_path, 'w', encoding='utf-8') as f:
            json.dump({"error": "لا توجد متطلبات وحصص لتوليد الجدول."}, f, ensure_ascii=False)
        return

    days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu']
    periods = [1, 2, 3, 4, 5, 6, 7] 

    model = cp_model.CpModel()
    schedule = {}

    # إنشاء المتغيرات لكل الاحتمالات
    for req in requirements:
        c, s, t = req['class'], req['subject'], req['teacher']
        for d in days:
            for p in periods:
                schedule[(c, s, t, d, p)] = model.NewBoolVar(f'sch_{c}_{s}_{t}_{d}_{p}')

    # ==========================================
    # 🔴 الخطوط الحمراء (Hard Constraints)
    # ==========================================
    
    # أ. الأستاذ والفصل لا يأخذان أكثر من محاضرة في نفس الوقت
    teachers = list(set(r['teacher'] for r in requirements))
    classes = list(set(r['class'] for r in requirements))
    
    for t in teachers:
        for d in days:
            for p in periods:
                model.AddAtMostOne(schedule[(r['class'], r['subject'], t, d, p)] for r in requirements if r['teacher'] == t)

    for c in classes:
        for d in days:
            for p in periods:
                model.AddAtMostOne(schedule[(c, r['subject'], r['teacher'], d, p)] for r in requirements if r['class'] == c)

    # ب. يجب توزيع جميع الحصص المطلوبة
    for req in requirements:
        c, s, t, sessions = req['class'], req['subject'], req['teacher'], req['sessions']
        model.Add(sum(schedule[(c, s, t, d, p)] for d in days for p in periods) == sessions)

    # ج. 🔴 التعديل الجديد: عدم تكرار نفس المادة لنفس الفصل في نفس اليوم
    for req in requirements:
        c, s, t, sessions = req['class'], req['subject'], req['teacher'], req['sessions']
        # الحماية: لو الحصص تفوق عدد الأيام (مثلاً 6 حصص في 5 أيام)، نسمح بحصتين في يوم واحد فقط للضرورة
        max_per_day = math.ceil(sessions / len(days))
        for d in days:
            model.Add(sum(schedule[(c, s, t, d, p)] for p in periods) <= max_per_day)


    # ==========================================
    # 🟢 القيود المرنة (Soft Constraints)
    # ==========================================
    objective_terms = []
    unwanted_dict = {str(t['name']): t.get('unwanted_slots', {}) for t in teachers_data}

    # 1. تفضيلات الأساتذة والأوقات المحظورة
    for req in requirements:
        c, s, t = req['class'], req['subject'], req['teacher']
        unwanted = unwanted_dict.get(t, {})
        for d in days:
            blocked_periods = unwanted.get(d, [])
            for p in periods:
                if p in blocked_periods:
                    objective_terms.append(-1000 * schedule[(c, s, t, d, p)]) # عقاب شديد
                else:
                    objective_terms.append(10 * schedule[(c, s, t, d, p)]) # مكافأة عادية

    # 2. تقارب المحاضرات وتسلسلها
    working = {}
    for t in teachers:
        for d in days:
            for p in periods:
                working[(t, d, p)] = model.NewBoolVar(f'work_{t}_{d}_{p}')
                model.Add(working[(t, d, p)] == sum(schedule[(r['class'], r['subject'], t, d, p)] for r in requirements if r['teacher'] == t))

    for t in teachers:
        for d in days:
            for i in range(len(periods) - 1):
                p1, p2 = periods[i], periods[i+1]
                consec = model.NewBoolVar(f'consec_{t}_{d}_{p1}')
                model.AddBoolAnd([working[(t, d, p1)], working[(t, d, p2)]]).OnlyEnforceIf(consec)
                model.AddBoolOr([working[(t, d, p1)].Not(), working[(t, d, p2)].Not()]).OnlyEnforceIf(consec.Not())
                objective_terms.append(50 * consec) # مكافأة قوية للحصص المتتالية

            for i in range(len(periods) - 2):
                p1, p2, p3 = periods[i], periods[i+1], periods[i+2]
                onegap = model.NewBoolVar(f'onegap_{t}_{d}_{p1}')
                model.AddBoolAnd([working[(t, d, p1)], working[(t, d, p2)].Not(), working[(t, d, p3)]]).OnlyEnforceIf(onegap)
                model.AddBoolOr([working[(t, d, p1)].Not(), working[(t, d, p2)], working[(t, d, p3)].Not()]).OnlyEnforceIf(onegap.Not())
                objective_terms.append(20 * onegap) 

    # 3. 🟢 التعديل الجديد: تجميع حصص الأستاذ في نفس اليوم قدر الإمكان
    teacher_day_active = {}
    for t in teachers:
        for d in days:
            teacher_day_active[(t, d)] = model.NewBoolVar(f't_day_active_{t}_{d}')
            # جلب كل الحصص الممكنة لهذا الأستاذ في هذا اليوم
            t_day_slots = [schedule[(r['class'], r['subject'], t, d, p)] for r in requirements if r['teacher'] == t for p in periods]
            if t_day_slots:
                # إذا كان يدرس أي حصة في هذا اليوم، يصبح المتغير true
                model.AddMaxEquality(teacher_day_active[(t, d)], t_day_slots)
                # عقاب: نخصم 40 نقطة على كل يوم عمل جديد يُفتح للأستاذ!
                # هذا سيجبر الخوارزمية على حشر كل حصصه في أقل عدد ممكن من الأيام
                objective_terms.append(-40 * teacher_day_active[(t, d)])

    # أمر الخوارزمية بجمع أكبر عدد ممكن من النقاط
    model.Maximize(sum(objective_terms))

    # ==========================================
    # ⚙️ تشغيل الخوارزمية وحفظ النتائج
    # ==========================================
    solver = cp_model.CpSolver()
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
            json.dump({"error": "مستحيل رياضياً: عدد الحصص المطلوبة أو قيود الأيام تفوق سعة الأسبوع المتاحة."}, f, ensure_ascii=False)

if __name__ == '__main__':
    main()