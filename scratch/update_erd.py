import urllib.request
import json
import ssl

ctx = ssl._create_unverified_context()

mermaid_erd = """erDiagram
    USERS ||--o| STUDENT_PROFILES : "1:1 profile"
    USERS ||--o| PARENT_PROFILES : "1:1 profile"
    SCHOOLS ||--o{ USERS : "belongs to"
    SCHOOLS ||--o{ SCHOOL_CLASSES : "has"
    GRADES ||--o{ SCHOOL_CLASSES : "defines"
    GRADES ||--o{ SUBJECTS : "curriculum"
    
    PARENT_PROFILES }|--|{ STUDENT_PROFILES : "parent_student (M:N)"
    SCHOOL_CLASSES ||--o{ STUDENT_PROFILES : "enrolls"
    
    SUBJECTS }|--|{ USERS : "teacher_subject (M:N)"
    STUDENT_PROFILES }|--|{ SUBJECTS : "student_subject (M:N)"
    
    SCHOOL_CLASSES ||--o{ TIMETABLES : "class_schedule"
    SUBJECTS ||--o{ TIMETABLES : "subject_schedule"
    USERS ||--o{ TIMETABLES : "teacher_schedule"
    
    SUBJECTS ||--o{ EXAMS : "has_exams"
    SCHOOL_CLASSES ||--o{ EXAMS : "exam_class"
    EXAMS ||--o{ MARKS : "has_marks"
    STUDENT_PROFILES ||--o{ MARKS : "receives_marks"
    
    STUDENT_PROFILES ||--o{ ATTENDANCES : "has_attendance"
    SUBJECTS ||--o{ ATTENDANCES : "subject_attendance"
    
    SUBJECTS ||--o{ ONLINE_EXAMS : "has_online_exams"
    ONLINE_EXAMS ||--o{ ONLINE_EXAM_QUESTIONS : "contains_questions"
    ONLINE_EXAMS ||--o{ ONLINE_EXAM_ATTEMPTS : "has_attempts"
    STUDENT_PROFILES ||--o{ ONLINE_EXAM_ATTEMPTS : "makes_attempts"
    
    SUBJECTS ||--o{ STUDY_MATERIALS : "has_materials"
    
    STUDENT_PROFILES ||--o{ FEES : "billed_fees"
    FEES ||--o{ FEE_PAYMENTS : "has_payments"
    STUDENT_PROFILES ||--o{ WALLET_TRANSACTIONS : "wallet_history"
    
    STUDENT_PROFILES ||--o{ LEAVE_REQUESTS : "submits_leave"
    USERS ||--o{ MESSAGES : "sends_messages"
    USERS ||--o{ MESSAGES : "receives_messages"
"""

puml_erd = """@startuml
skinparam linetype ortho
skinparam packageStyle rectangle
skinparam backgroundColor #FFFFFF

entity "schools" as schools {
  * id : BIGINT (PK)
  --
  name : VARCHAR(255)
  code : VARCHAR(50)
  phone : VARCHAR(20)
}

entity "users" as users {
  * id : BIGINT (PK)
  --
  school_id : BIGINT (FK)
  name : VARCHAR(255)
  email : VARCHAR(255)
  password : VARCHAR(255)
  phone : VARCHAR(20)
}

entity "student_profiles" as student_profiles {
  * id : BIGINT (PK)
  --
  user_id : BIGINT (FK)
  class_id : BIGINT (FK)
  grade_id : BIGINT (FK)
  student_number : VARCHAR(50)
  wallet_balance : DECIMAL(10,2)
  wallet_pin : VARCHAR(255)
}

entity "parent_profiles" as parent_profiles {
  * id : BIGINT (PK)
  --
  user_id : BIGINT (FK)
  national_id : VARCHAR(50)
  occupation : VARCHAR(255)
}

entity "parent_student" as parent_student {
  * parent_id : BIGINT (FK)
  * student_id : BIGINT (FK)
  relationship : VARCHAR(50)
}

entity "grades" as grades {
  * id : BIGINT (PK)
  --
  name : VARCHAR(100)
  code : VARCHAR(20)
}

entity "school_classes" as school_classes {
  * id : BIGINT (PK)
  --
  grade_id : BIGINT (FK)
  supervisor_id : BIGINT (FK)
  name : VARCHAR(100)
  capacity : INT
}

entity "subjects" as subjects {
  * id : BIGINT (PK)
  --
  grade_id : BIGINT (FK)
  name : VARCHAR(150)
  code : VARCHAR(30)
  max_marks : INT
  pass_marks : INT
}

entity "timetables" as timetables {
  * id : BIGINT (PK)
  --
  class_id : BIGINT (FK)
  subject_id : BIGINT (FK)
  teacher_id : BIGINT (FK)
  day_of_week : ENUM
  start_time : TIME
  end_time : TIME
}

entity "exams" as exams {
  * id : BIGINT (PK)
  --
  subject_id : BIGINT (FK)
  class_id : BIGINT (FK)
  name : VARCHAR(150)
  exam_date : DATE
  total_marks : INT
}

entity "marks" as marks {
  * id : BIGINT (PK)
  --
  exam_id : BIGINT (FK)
  student_id : BIGINT (FK)
  marks_obtained : DECIMAL(5,2)
  remarks : VARCHAR(255)
}

entity "attendances" as attendances {
  * id : BIGINT (PK)
  --
  student_id : BIGINT (FK)
  subject_id : BIGINT (FK)
  date : DATE
  status : ENUM
}

entity "online_exams" as online_exams {
  * id : BIGINT (PK)
  --
  subject_id : BIGINT (FK)
  teacher_id : BIGINT (FK)
  title : VARCHAR(255)
  duration_minutes : INT
  start_time : DATETIME
  end_time : DATETIME
}

entity "online_exam_questions" as online_exam_questions {
  * id : BIGINT (PK)
  --
  online_exam_id : BIGINT (FK)
  question_text : TEXT
  options : JSON
  correct_option : VARCHAR(10)
  marks : INT
}

entity "online_exam_attempts" as online_exam_attempts {
  * id : BIGINT (PK)
  --
  online_exam_id : BIGINT (FK)
  student_id : BIGINT (FK)
  score_obtained : DECIMAL(5,2)
  submitted_at : DATETIME
}

entity "fees" as fees {
  * id : BIGINT (PK)
  --
  student_id : BIGINT (FK)
  title : VARCHAR(255)
  amount : DECIMAL(10,2)
  due_date : DATE
}

entity "fee_payments" as fee_payments {
  * id : BIGINT (PK)
  --
  fee_id : BIGINT (FK)
  amount_paid : DECIMAL(10,2)
  payment_method : ENUM
  paid_at : TIMESTAMP
}

entity "wallet_transactions" as wallet_transactions {
  * id : BIGINT (PK)
  --
  student_id : BIGINT (FK)
  amount : DECIMAL(10,2)
  type : ENUM
  description : VARCHAR(255)
}

entity "leave_requests" as leave_requests {
  * id : BIGINT (PK)
  --
  student_id : BIGINT (FK)
  reason : TEXT
  start_date : DATE
  end_date : DATE
  status : ENUM
}

entity "messages" as messages {
  * id : BIGINT (PK)
  --
  sender_id : BIGINT (FK)
  receiver_id : BIGINT (FK)
  body : TEXT
  is_read : BOOLEAN
}

schools ||--o{ users
users ||--o| student_profiles
users ||--o| parent_profiles
parent_profiles ||--|{ parent_student
student_profiles ||--|{ parent_student
grades ||--o{ school_classes
grades ||--o{ subjects
school_classes ||--o{ student_profiles
school_classes ||--o{ timetables
subjects ||--o{ timetables
users ||--o{ timetables
subjects ||--o{ exams
exams ||--o{ marks
student_profiles ||--o{ marks
student_profiles ||--o{ attendances
subjects ||--o{ attendances
subjects ||--o{ online_exams
online_exams ||--o{ online_exam_questions
online_exams ||--o{ online_exam_attempts
student_profiles ||--o{ online_exam_attempts
student_profiles ||--o{ fees
fees ||--o{ fee_payments
student_profiles ||--o{ wallet_transactions
student_profiles ||--o{ leave_requests
users ||--o{ messages

@enduml"""

# Save mmd and puml files
with open('docs/erd_diagram.mmd', 'w', encoding='utf-8') as f:
    f.write(mermaid_erd)

with open('docs/erd_diagram.puml', 'w', encoding='utf-8') as f:
    f.write(puml_erd)

print('docs/erd_diagram.mmd and docs/erd_diagram.puml created successfully')

# Generate PNG via Kroki
req = urllib.request.Request(
    'https://kroki.io/plantuml/png',
    data=json.dumps({'diagram_source': puml_erd, 'output_format': 'png'}).encode('utf-8'),
    headers={
        'Content-Type': 'application/json',
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
    }
)

try:
    with urllib.request.urlopen(req, context=ctx) as resp:
        with open('docs/erd_diagram.png', 'wb') as out:
            out.write(resp.read())
    print('docs/erd_diagram.png generated successfully!')
except Exception as e:
    print('Error generating ERD PNG:', e)
