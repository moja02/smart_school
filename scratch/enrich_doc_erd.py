import re

with open('docs/erd_diagram.mmd', 'r', encoding='utf-8') as f:
    mermaid_erd_code = f.read()

with open('docs/erd_diagram.puml', 'r', encoding='utf-8') as f:
    puml_erd_code = f.read()

chapter3_content = f"""# الفصل الثالث: مرحلة التصميم (Phase 3: System Design)

## 3.1 مقدمة
يتناول هذا الفصل مرحلة التصميم التفصيلي لنظام "المدرسة الذكية (Smart School)"، والتي تُشكل الجسر الناقل بين مرحلة تحليل المتطلبات الوظيفية وغير الوظيفية ومرحلة التنفيذ البرمجي. تتضمن هذه المرحلة بناء الهياكل التصميمية الأساسية لقواعد البيانات، وتحديد علاقات الكيانات (ERD)، وصياغة قاموس البيانات الشامل، وتوثيق المعمارية العامة ومخططات الفئات والتتابع للعمليات الحساسة في النظام.

---

## 3.2 مخطط علاقات الكيانات (Entity-Relationship Diagram - ERD)

يمثل مخطط **ERD** البنية الهيكلية لقاعدة البيانات العلاقية لنظام المدرسة الذكية، حيث يربط بين **21 كياناً رئيسياً** تُغطي جميع العمليات الإدارية، الأكاديمية، المالية، والتواصلية. 

تتوزع العلاقات في النظام بين:
* **علاقات رأس برأس (1:1):** مثل ربط مستخدم النظام (`users`) بملف الطالب (`student_profiles`) أو ملف ولي الأمر (`parent_profiles`).
* **علاقات رأس بأطراف (1:M):** مثل ارتباط الفصل الدراسي بدفعة من الطلاب، وارتباط المادة بجدول الامتحانات والدرجات وسجلات الحضور.
* **علاقات أطراف بأطراف (M:N):** مثل ربط ولي الأمر بعدة أبناء (`parent_student`)، وإسناد أكثر من مادة لأكثر من معلم (`teacher_subject`)، وتسجيل الطلاب في عدة مواد (`student_subject`).

> **الملفات المرئية المرفقة للمخطط:**
> * صورة المخطط عالية الدقة: [erd_diagram.png](file:///c:/Users/HP/Documents/GitHub/smart_school/docs/erd_diagram.png)
> * كود PlantUML للمخطط: [erd_diagram.puml](file:///c:/Users/HP/Documents/GitHub/smart_school/docs/erd_diagram.puml)
> * كود Mermaid للمخطط: [erd_diagram.mmd](file:///c:/Users/HP/Documents/GitHub/smart_school/docs/erd_diagram.mmd)

### 3.2.1 كود مخطط ERD بلغة Mermaid

```mermaid
{mermaid_erd_code}
```

### 3.2.2 كود مخطط ERD بلغة PlantUML

```plantuml
{puml_erd_code}
```

---

## 3.3 قاموس البيانات وتوصيف كيانات النظام (Data Dictionary & Entity Specifications)

يوضح قاموس البيانات التفاصيل الدقيقة لكل جدول في قاعدة البيانات، متضمناً أسماء الحقول، أنواع البيانات، القيود المطبقة (Primary Key PK، Foreign Key FK، Unique)، والوصف التشغيلي لكل حقل.

### 3.3.1 كيان المدارس والمؤسسات (schools)
يمثل المنشأة التعليمية الأساسية التي تتبع لها جميع البيانات.

| اسم الحقل | نوع البيانات | القيود | الوصف |
|---|---|---|---|
| `id` | BIGINT | PK, Auto Increment | المعرّف الفريد للمدرسة |
| `name` | VARCHAR(255) | NOT NULL | اسم المدرسة أو المؤسسة |
| `code` | VARCHAR(50) | UNIQUE, NOT NULL | الرمز التعريفي للمدرسة |
| `phone` | VARCHAR(20) | NULLABLE | رقم هاتف التواصل |
| `address` | TEXT | NULLABLE | العنوان الجغرافي للمدرسة |
| `created_at` | TIMESTAMP | NULLABLE | تاريخ التسجيل |

---

### 3.3.2 كيان المستخدمين (users)
يخزن بيانات الحسابات لجميع الفئات (Admin, Teacher, Student, Parent).

| اسم الحقل | نوع البيانات | القيود | الوصف |
|---|---|---|---|
| `id` | BIGINT | PK, Auto Increment | المعرّف الفريد للمستخدم |
| `school_id` | BIGINT | FK -> schools(id) | المدرسة التابع لها |
| `name` | VARCHAR(255) | NOT NULL | الاسم الكامل |
| `email` | VARCHAR(255) | UNIQUE, NOT NULL | البريد الإلكتروني لتسجيل الدخول |
| `password` | VARCHAR(255) | NOT NULL | كلمة المرور المشفرة بـ Bcrypt |
| `phone` | VARCHAR(20) | NULLABLE | رقم الهاتف الجوال |
| `avatar` | VARCHAR(255) | NULLABLE | مسار صورة الملف الشخصي |
| `status` | ENUM('active','inactive') | DEFAULT 'active' | حالة الحساب |
| `created_at` | TIMESTAMP | NULLABLE | تاريخ إنشاء الحساب |

---

### 3.3.3 كيان ملفات الطلاب والمحفظة الإلكترونية (student_profiles)
يحتوي على البيانات الأكاديمية والشخصية للطلاب ورصيد المحفظة الإلكترونية.

| اسم الحقل | نوع البيانات | القيود | الوصف |
|---|---|---|---|
| `id` | BIGINT | PK, Auto Increment | المعرّف الفريد لملف الطالب |
| `user_id` | BIGINT | FK -> users(id), UNIQUE | الحساب الأساسي المرتبط (1:1) |
| `grade_id` | BIGINT | FK -> grades(id) | المرحلة الدراسية |
| `class_id` | BIGINT | FK -> school_classes(id) | الفصل الدراسي المسجل فيه |
| `student_number` | VARCHAR(50) | UNIQUE, NOT NULL | الرقم الأكاديمي الرقمي |
| `date_of_birth` | DATE | NOT NULL | تاريخ الميلاد |
| `gender` | ENUM('male','female') | NOT NULL | الجنس |
| `address` | TEXT | NULLABLE | عنوان السكن |
| `wallet_balance` | DECIMAL(10,2) | DEFAULT 0.00 | رصيد المحفظة الإلكترونية |
| `wallet_pin` | VARCHAR(255) | NULLABLE | رمز الأمان المشفر للشراء (PIN) |
| `enrollment_date` | DATE | NOT NULL | تاريخ قيد الطالب بالمدرسة |

---

### 3.3.4 كيان ملفات أولياء الأمور (parent_profiles)
يخزن البيانات الخاصة بولي الأمر للربط بالأبناء والمتابعة المالية والأكاديمية.

| اسم الحقل | نوع البيانات | القيود | الوصف |
|---|---|---|---|
| `id` | BIGINT | PK, Auto Increment | المعرّف الفريد لملف ولي الأمر |
| `user_id` | BIGINT | FK -> users(id), UNIQUE | الحساب الأساسي المرتبط (1:1) |
| `national_id` | VARCHAR(50) | UNIQUE, NOT NULL | رقم الهوية الوطنية أو إثبات الشخصية |
| `occupation` | VARCHAR(255) | NULLABLE | المهنة أو جهة العمل |
| `emergency_phone` | VARCHAR(20) | NULLABLE | رقم هاتف للتواصل في الطوارئ |

---

### 3.3.5 كيان الربط بين أولياء الأمور والأبناء (parent_student)
جدول وسيط يمثل علاقة المتابعة المتعددة (M:N) بين أولياء الأمور والطلاب.

| اسم الحقل | نوع البيانات | القيود | الوصف |
|---|---|---|---|
| `parent_id` | BIGINT | FK -> parent_profiles(id) | معرّف ولي الأمر |
| `student_id` | BIGINT | FK -> student_profiles(id) | معرّف الطالب (الابن) |
| `relationship` | VARCHAR(50) | DEFAULT 'father' | درجة القرابة (أب، أم، وصي) |

---

### 3.3.6 كيان المراحل الدراسية (grades)
يمثل الصفوف والمراحل التعليمية (مثل: الصف الأول الثانوي، الصف الثاني الإعدادي).

| اسم الحقل | نوع البيانات | القيود | الوصف |
|---|---|---|---|
| `id` | BIGINT | PK, Auto Increment | المعرّف الفريد للمرحلة |
| `name` | VARCHAR(100) | NOT NULL | اسم المرحلة |
| `code` | VARCHAR(20) | UNIQUE, NOT NULL | رمز المرحلة |

---

### 3.3.7 كيان الفصول الدراسية (school_classes)
يمثل شعب الفصول المدرسية (مثل: فصل 1/أ، فصل 2/ب).

| اسم الحقل | نوع البيانات | القيود | الوصف |
|---|---|---|---|
| `id` | BIGINT | PK, Auto Increment | المعرّف الفريد للفصل |
| `grade_id` | BIGINT | FK -> grades(id) | المرحلة الدراسية التابع لها |
| `supervisor_id` | BIGINT | FK -> users(id), NULLABLE | المعلم المشرف على الفصل |
| `name` | VARCHAR(100) | NOT NULL | اسم شعبة الفصل |
| `capacity` | INT | DEFAULT 30 | الطاقة الاستيعابية القصوى |

---

### 3.3.8 كيان المواد الدراسية (subjects)
يمثل المواد الدراسية المدرجة في المناهج والمراحل المختلفة.

| اسم الحقل | نوع البيانات | القيود | الوصف |
|---|---|---|---|
| `id` | BIGINT | PK, Auto Increment | المعرّف الفريد للمادة |
| `grade_id` | BIGINT | FK -> grades(id) | المرحلة الدراسية التابعة لها |
| `name` | VARCHAR(150) | NOT NULL | اسم المادة (مثل: الرياضيات، الفيزياء) |
| `code` | VARCHAR(30) | UNIQUE, NOT NULL | رمز المادة |
| `max_marks` | INT | DEFAULT 100 | الدرجة العظمى للمادة |
| `pass_marks` | INT | DEFAULT 50 | درجة النجاح الدنيا |

---

### 3.3.9 كيان الجداول المدرسية (timetables)
يخزن توزيع الحصص الدراسية والمواد والمعلمين على أيام الأسبوع.

| اسم الحقل | نوع البيانات | القيود | الوصف |
|---|---|---|---|
| `id` | BIGINT | PK, Auto Increment | المعرّف الفريد للحصة |
| `class_id` | BIGINT | FK -> school_classes(id) | الفصل المستهدف |
| `subject_id` | BIGINT | FK -> subjects(id) | المادة |
| `teacher_id` | BIGINT | FK -> users(id) | المعلم المدرس للحصة |
| `day_of_week` | ENUM('Sunday','Monday','Tuesday','Wednesday','Thursday') | NOT NULL | يوم الحصة |
| `start_time` | TIME | NOT NULL | وقت بداية الحصة |
| `end_time` | TIME | NOT NULL | وقت نهاية الحصة |
| `room_number` | VARCHAR(50) | NULLABLE | رقم القاعة الدراسية |

---

### 3.3.10 كيان الامتحانات المدرسية والمركزية (exams)
يمثل الجدول المعتمد للامتحانات المدرسية الرسمية.

| اسم الحقل | نوع البيانات | القيود | الوصف |
|---|---|---|---|
| `id` | BIGINT | PK, Auto Increment | المعرّف الفريد للامتحان |
| `subject_id` | BIGINT | FK -> subjects(id) | المادة الدراسية |
| `class_id` | BIGINT | FK -> school_classes(id) | الفصل المشمول |
| `name` | VARCHAR(150) | NOT NULL | عنوان الامتحان (مثل: امتحان نصف العام) |
| `exam_date` | DATE | NOT NULL | تاريخ إجراء الامتحان |
| `start_time` | TIME | NOT NULL | وقت بداية الامتحان |
| `end_time` | TIME | NOT NULL | وقت النهاية |
| `total_marks` | INT | NOT NULL | الدرجة العظمى للامتحان |

---

### 3.3.11 كيان كشوف الدرجات والنتائج (marks)
يخزن درجات الطلاب المحصلة في الامتحانات المعتمدة.

| اسم الحقل | نوع البيانات | القيود | الوصف |
|---|---|---|---|
| `id` | BIGINT | PK, Auto Increment | المعرّف الفريد للدرجة |
| `exam_id` | BIGINT | FK -> exams(id) | الامتحان المعني |
| `student_id` | BIGINT | FK -> student_profiles(id) | الطالب المرصود له |
| `marks_obtained` | DECIMAL(5,2) | NOT NULL | الدرجة المحصلة فعلياً |
| `remarks` | VARCHAR(255) | NULLABLE | ملاحظات المعلم أو المصحح |

---

### 3.3.12 كيان سجلات الحضور والغياب (attendances)
يسجل حضور أو غياب الطالب في كل حصة دراسية يومياً.

| اسم الحقل | نوع البيانات | القيود | الوصف |
|---|---|---|---|
| `id` | BIGINT | PK, Auto Increment | المعرّف الفريد للسجل |
| `student_id` | BIGINT | FK -> student_profiles(id) | الطالب |
| `subject_id` | BIGINT | FK -> subjects(id) | المادة / الحصة |
| `teacher_id` | BIGINT | FK -> users(id) | المعلم الذي قام بالرصد |
| `date` | DATE | NOT NULL | تاريخ اليوم |
| `status` | ENUM('present','absent','late','excused') | NOT NULL | حالة الحضور |
| `note` | VARCHAR(255) | NULLABLE | مبرر الغياب أو التأخير |

---

### 3.3.13 كيان الامتحانات الإلكترونية (online_exams)
يمثل الامتحانات التفاعلية التي ينشئها المعلم للطلاب عبر البوابة.

| اسم الحقل | نوع البيانات | القيود | الوصف |
|---|---|---|---|
| `id` | BIGINT | PK, Auto Increment | المعرّف الفريد للامتحان الأونلاين |
| `subject_id` | BIGINT | FK -> subjects(id) | المادة |
| `teacher_id` | BIGINT | FK -> users(id) | المعلم المنشئ |
| `class_id` | BIGINT | FK -> school_classes(id) | الفصل المستهدف |
| `title` | VARCHAR(255) | NOT NULL | عنوان الامتحان التفاعلي |
| `duration_minutes` | INT | NOT NULL | مدة الامتحان بالدقائق |
| `start_time` | DATETIME | NOT NULL | وقت فتح الامتحان |
| `end_time` | DATETIME | NOT NULL | وقت إغلاق الامتحان |
| `is_published` | BOOLEAN | DEFAULT FALSE | حالة النشر للطلاب |

---

### 3.3.14 كيان أسئلة الامتحانات الإلكترونية (online_exam_questions)
يخزن بنك الأسئلة والخيارات والدرجات والتصحيح التلقائي.

| اسم الحقل | نوع البيانات | القيود | الوصف |
|---|---|---|---|
| `id` | BIGINT | PK, Auto Increment | المعرّف الفريد للسؤال |
| `online_exam_id` | BIGINT | FK -> online_exams(id) | الامتحان التابع له |
| `question_text` | TEXT | NOT NULL | نص السؤال |
| `options` | JSON | NOT NULL | خيارات الإجابة بصيغة JSON |
| `correct_option` | VARCHAR(10) | NOT NULL | مفتاح الخيار الصحيح |
| `marks` | INT | DEFAULT 1 | درجة السؤال |

---

### 3.3.15 كيان محاولات الإجابة للامتحانات الإلكترونية (online_exam_attempts)
يسجل أداء الطالب وإجاباته والنتيجة التلقائية المحسوبة.

| اسم الحقل | نوع البيانات | القيود | الوصف |
|---|---|---|---|
| `id` | BIGINT | PK, Auto Increment | المعرّف الفريد للمحاولة |
| `online_exam_id` | BIGINT | FK -> online_exams(id) | الامتحان |
| `student_id` | BIGINT | FK -> student_profiles(id) | الطالب المؤدي |
| `answers` | JSON | NULLABLE | إجابات الطالب المرسلة |
| `score_obtained` | DECIMAL(5,2) | DEFAULT 0.00 | النتيجة النهائية المحسوبة آلياً |
| `started_at` | DATETIME | NOT NULL | وقت بدء الامتحان |
| `submitted_at` | DATETIME | NULLABLE | وقت تسليم الامتحان النهائي |

---

### 3.3.16 كيان الرسوم المدرسية والأقساط (fees)
يتتبع الأقساط والرسوم المستحقة على كل طالب.

| اسم الحقل | نوع البيانات | القيود | الوصف |
|---|---|---|---|
| `id` | BIGINT | PK, Auto Increment | المعرّف الفريد للرسوم |
| `student_id` | BIGINT | FK -> student_profiles(id) | الطالب المستحق عليه |
| `title` | VARCHAR(255) | NOT NULL | عنوان الرسوم (مثل: قسط الفصل الأول) |
| `amount` | DECIMAL(10,2) | NOT NULL | المبلغ الكلي المستحق |
| `due_date` | DATE | NOT NULL | آخر موعد للسداد |
| `status` | ENUM('unpaid','partially_paid','paid') | DEFAULT 'unpaid' | حالة السداد |

---

### 3.3.17 كيان مدفوعات الرسوم (fee_payments)
يسجل عمليات الدفع والسداد للرسوم المدرسية.

| اسم الحقل | نوع البيانات | القيود | الوصف |
|---|---|---|---|
| `id` | BIGINT | PK, Auto Increment | المعرّف الفريد لعملية الدفع |
| `fee_id` | BIGINT | FK -> fees(id) | الرسوم المستهدفة |
| `amount_paid` | DECIMAL(10,2) | NOT NULL | المبلغ المدفوع |
| `payment_method` | ENUM('e_wallet','cash','bank_transfer') | NOT NULL | طريقة الدفع |
| `transaction_reference` | VARCHAR(100) | NULLABLE | مرجع المعاملة المالية |
| `paid_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | تاريخ ووقت الدفع |

---

### 3.3.18 كيان معاملات المحفظة الإلكترونية (wallet_transactions)
يسجل الشحن والسحب والاسترجاع في محفظة الطالب الإلكترونية.

| اسم الحقل | نوع البيانات | القيود | الوصف |
|---|---|---|---|
| `id` | BIGINT | PK, Auto Increment | المعرّف الفريد للمُعاملة |
| `student_id` | BIGINT | FK -> student_profiles(id) | الطالب صاحب المحفظة |
| `amount` | DECIMAL(10,2) | NOT NULL | قيمة المعاملة |
| `type` | ENUM('credit','debit') | NOT NULL | نوع العملية (إيداع / خصم) |
| `description` | VARCHAR(255) | NOT NULL | بيان العملية |
| `performed_by` | BIGINT | FK -> users(id) | المستخدم الذي نفّذ العملية |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | وقت العملية |

---

### 3.3.19 كيان طلبات الإجازات والاستئذان (leave_requests)
يتيح لولي الأمر تقديم مبررات غياب الطالب وإرفاق التقارير الطبية.

| اسم الحقل | نوع البيانات | القيود | الوصف |
|---|---|---|---|
| `id` | BIGINT | PK, Auto Increment | المعرّف الفريد للطلب |
| `student_id` | BIGINT | FK -> student_profiles(id) | الطالب المعني |
| `submitted_by` | BIGINT | FK -> users(id) | مقدم الطلب (ولي الأمر) |
| `reason` | TEXT | NOT NULL | سبب طلب الإجازة أو الغياب |
| `attachment` | VARCHAR(255) | NULLABLE | مسار الملف المرفق (تقرير طبي) |
| `start_date` | DATE | NOT NULL | تاريخ بدء الإجازة |
| `end_date` | DATE | NOT NULL | تاريخ نهاية الإجازة |
| `status` | ENUM('pending','approved','rejected') | DEFAULT 'pending' | حالة الطلب |

---

### 3.3.20 كيان الرسائل والتراسل الداخلي (messages)
يمثل المحادثات المباشرة والملاحظات السلوكية بين المستخدمين.

| اسم الحقل | نوع البيانات | القيود | الوصف |
|---|---|---|---|
| `id` | BIGINT | PK, Auto Increment | المعرّف الفريد للرسالة |
| `sender_id` | BIGINT | FK -> users(id) | المرسل |
| `receiver_id` | BIGINT | FK -> users(id) | المستلم |
| `body` | TEXT | NOT NULL | نص الرسالة |
| `is_read` | BOOLEAN | DEFAULT FALSE | هل قُرئت الرسالة |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | وقت الإرسال |

---

### 3.3.21 كيان المحتوى الدراسي والمذكرات (study_materials)
يتيح للمعلم رفع وتشارك الكتب والمذكرات والمستندات التعليمية.

| اسم الحقل | نوع البيانات | القيود | الوصف |
|---|---|---|---|
| `id` | BIGINT | PK, Auto Increment | المعرّف الفريد للمحتوى |
| `subject_id` | BIGINT | FK -> subjects(id) | المادة الدراسية |
| `teacher_id` | BIGINT | FK -> users(id) | المعلم الرافع |
| `class_id` | BIGINT | FK -> school_classes(id) | الفصل المستهدف |
| `title` | VARCHAR(255) | NOT NULL | عنوان المذكرة أو الدرس |
| `file_path` | VARCHAR(255) | NOT NULL | مسار حفظ الملف |
| `file_type` | ENUM('pdf','doc','video','link','image') | NOT NULL | نوع الملف |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | تاريخ الرفع |

---

## 3.4 جدول توصيف العلاقات بين الكيانات (Database Relationships Summary)

| الكيان الأول | الكيان الثاني | نوع العلاقة | وصف التفاعل والتكامل المرجعي |
|---|---|---|---|
| `schools` | `users` | 1:M | المدرسة الواحدة تحتضن عدة مستخدمين بمختلف الأدوار |
| `users` | `student_profiles` | 1:1 | مستخدم الحساب يرتبط بملف طالب واحد فقط |
| `users` | `parent_profiles` | 1:1 | مستخدم الحساب يرتبط بملف ولي أمر واحد فقط |
| `parent_profiles` | `student_profiles` | M:N | ولي الأمر يتابع عدة أبناء، والابن قد يكون له أكثر من ولي أمر (عبر `parent_student`) |
| `grades` | `school_classes` | 1:M | المرحلة التعليمية تضم عدة شعب وفصول |
| `school_classes` | `student_profiles` | 1:M | الفصل الدراسي يتكون من عدة طلاب |
| `subjects` | `users` | M:N | المعلم يدرّس عدة مواد، والمادة قد يدرّسها عدة معلمين (عبر `teacher_subject`) |
| `subjects` | `student_profiles` | M:N | الطالب يدرس عدة مواد (عبر `student_subject`) |
| `school_classes` | `timetables` | 1:M | الفصل له جدول أسبوعي يضم عدة حصص |
| `subjects` | `exams` | 1:M | المادة الواحدة لها عدة امتحانات على مدار العام |
| `exams` | `marks` | 1:M | الامتحان يضم درجات رصدت لعدة طلاب |
| `student_profiles` | `attendances` | 1:M | الطالب له سجلات حضور يومية متتابعة |
| `subjects` | `online_exams` | 1:M | المادة تحتوي على عدة امتحانات أونلاين تفاعلية |
| `online_exams` | `online_exam_questions` | 1:M | الامتحان الإلكتروني يتكون من عدة أسئلة تفاعلية |
| `online_exams` | `online_exam_attempts` | 1:M | الامتحان الإلكتروني يُنفذ بمحاولات متعددة من الطلاب |
| `student_profiles` | `fees` | 1:M | الطالب تُفرض عليه مطالبات رسوم متعددة |
| `fees` | `fee_payments` | 1:M | فاتورة الرسوم الواحدة قد تُسدد على دفعات متعددة |
| `student_profiles` | `wallet_transactions` | 1:M | محفظة الطالب تتضمن عدة عمليات شحن وخصم |
| `student_profiles` | `leave_requests` | 1:M | الطالب ترفع له طلبات إجازة واستئذان متعددة |
| `users` | `messages` | M:N | المستخدمون يتبادلون الرسائل التفاعلية المباشرة |
"""

with open('docs/Smart_School_Documentation.md', 'r', encoding='utf-8') as f:
    doc_text = f.read()

target_heading = '# الفصل الثالث: مرحلة التصميم'
if target_heading not in doc_text:
    target_heading = '# الفصل الثالث: مرحلة التصميم (Phase 3: System Design)'

pos = doc_text.find(target_heading)
if pos == -1:
    print('Error: Could not find Chapter 3 heading!')
else:
    # Find where Chapter 4 starts
    pos_ch4 = doc_text.find('# الفصل الرابع: مرحلة التنفيذ والاختبار', pos)
    if pos_ch4 == -1:
        pos_ch4 = doc_text.find('# الفصل الرابع', pos)

    if pos_ch4 != -1:
        # Keep page break before Chapter 4
        pb_pos = doc_text.rfind('<div style="page-break-after: always;"></div>', pos, pos_ch4)
        if pb_pos != -1:
            end_cut = pb_pos
        else:
            end_cut = pos_ch4
        
        new_doc_text = doc_text[:pos] + chapter3_content.strip() + '\n\n</div>\n\n<div style="page-break-after: always;"></div>\n\n' + doc_text[end_cut:]
        with open('docs/Smart_School_Documentation.md', 'w', encoding='utf-8') as f:
            f.write(new_doc_text)
        print('Documentation updated successfully with enriched ERD and Data Dictionary!')
    else:
        print('Error: Could not find Chapter 4 heading!')
