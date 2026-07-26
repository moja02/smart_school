import sys
import json
import random

def main():
    if len(sys.argv) < 2:
        print("[]")
        return

    json_path = sys.argv[1]
    try:
        with open(json_path, 'r', encoding='utf-8') as f:
            constraints = json.load(f)
    except Exception as e:
        print("[]")
        return

    # Data structures
    teachers = constraints.get('teachers', [])
    classes = constraints.get('classes', [])
    assignments = constraints.get('assignments', [])
    preferences = constraints.get('preferences', [])
    
    days = ["الأحد", "الإثنين", "الثلاثاء", "الأربعاء", "الخميس"]
    periods = [1, 2, 3, 4, 5, 6]
    
    # Process preferences (blocked times for teachers)
    teacher_unavailability = {}
    for pref in preferences:
        tid = pref.get('teacher_id')
        if tid not in teacher_unavailability:
            teacher_unavailability[tid] = []
        
        day = pref.get('day_name')
        if pref.get('is_day_off'):
            for p in periods:
                teacher_unavailability[tid].append((day, p))
        else:
            blocked = pref.get('blocked_periods')
            if isinstance(blocked, list):
                for p in blocked:
                    teacher_unavailability[tid].append((day, int(p)))
            elif isinstance(blocked, str) and blocked:
                try:
                    blocked_arr = json.loads(blocked)
                    for p in blocked_arr:
                        teacher_unavailability[tid].append((day, int(p)))
                except:
                    pass

    # Build the schedule
    # Format: schedule[(class_id, day, period)] = (teacher_id, subject_id)
    # Track teacher schedules: teacher_schedule[(teacher_id, day, period)] = class_id
    schedule = {}
    teacher_schedule = {}

    class_assignments = {}
    for assign in assignments:
        cid = assign.get('section_id')
        if not cid:
            continue
        if cid not in class_assignments:
            class_assignments[cid] = []
        class_assignments[cid].append({
            'teacher_id': assign.get('teacher_id'),
            'subject_id': assign.get('subject_id')
        })

    # Greedy Assignment
    for cid in classes:
        available_slots = [(d, p) for d in days for p in periods]
        c_assigns = class_assignments.get(cid, [])
        if not c_assigns:
            continue
            
        random.shuffle(available_slots)
        
        for slot in available_slots:
            d, p = slot
            random.shuffle(c_assigns)
            
            for req in c_assigns:
                tid = req['teacher_id']
                subid = req['subject_id']
                
                if (d, p) in teacher_unavailability.get(tid, []):
                    continue
                if (tid, d, p) in teacher_schedule:
                    continue
                    
                schedule[(cid, d, p)] = (tid, subid)
                teacher_schedule[(tid, d, p)] = cid
                break

    output_schedule = []
    for (cid, d, p), (tid, subid) in schedule.items():
        output_schedule.append({
            "class_id": cid,
            "subject_id": subid,
            "teacher_id": tid,
            "day": d,
            "period": p
        })

    print(json.dumps(output_schedule))

if __name__ == "__main__":
    main()
