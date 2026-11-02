import re

mermaid_code = """```mermaid
graph TD
    classDef actor fill:#dae8fc,stroke:#6c8ebf,stroke-width:2px,font-weight:bold;
    classDef common fill:#e1d5e7,stroke:#9673a6,stroke-width:2px,font-weight:bold;
    classDef admin fill:#dae8fc,stroke:#6c8ebf,stroke-width:1px;
    classDef teacher fill:#d5e8d4,stroke:#82b366,stroke-width:1px;
    classDef student fill:#fff2cc,stroke:#d6b656,stroke-width:1px;
    classDef parent fill:#f8cecc,stroke:#b85450,stroke-width:1px;

    subgraph System["Smart School System — نظام المدرسة الذكية"]
        subgraph Common["الوظائف العامة"]
            UC_Login["تسجيل الدخول - Login"]:::common
            UC_Profile["إدارة الملف الشخصي - Manage Profile"]:::common
            UC_Notif["استقبال الإشعارات الفورية - Receive Notifications"]:::common
        end

        subgraph Admin_UCs["حالات استخدام مدير النظام - Admin"]
            UC01["UC-01: إدارة المستخدمين والأدوار"]:::admin
            UC02["UC-02: إدارة الهيكل المدرسي والفصول"]:::admin
            UC03["UC-03: إدارة المواد وتوزيع الدرجات"]:::admin
            UC04["UC-04: إسناد المواد للمعلمين"]:::admin
            UC05["UC-05: إدارة قبول الطلاب والتسكين"]:::admin
            UC06["UC-06: ربط أولياء الأمور بالأبناء"]:::admin
            UC07["UC-07: إنشاء وإدارة الجداول الآلية"]:::admin
            UC08["UC-08: إدارة الامتحانات والجداول"]:::admin
            UC09["UC-09: إدارة المالية والرسوم والمحافظ"]:::admin
            UC10["UC-10: إدارة طلبات الإجازات والاستئذان"]:::admin
            UC11["UC-11: ترفيع الطلاب وإغلاق العام"]:::admin
            UC12["UC-12: لوحة التحكم والتقارير المتقدمة"]:::admin
        end

        subgraph Teacher_UCs["حالات استخدام المعلم - Teacher"]
            UC13["UC-13: استعراض الجدول الأسبوعي"]:::teacher
            UC14["UC-14: تسجيل الحضور والغياب اليومي"]:::teacher
            UC15["UC-15: إدارة التقييمات وأعمال السنة"]:::teacher
            UC16["UC-16: إدخال واعتماد الدرجات النهائية"]:::teacher
            UC17["UC-17: إدارة الدروس والواجبات المنزلية"]:::teacher
            UC18["UC-18: إدارة الكويزات وبنك الأسئلة"]:::teacher
            UC19["UC-19: إنشاء وإدارة الامتحانات الإلكترونية"]:::teacher
            UC20["UC-20: إدارة المذكرات والملفات الدراسية"]:::teacher
            UC21["UC-21: استخراج التقارير الأكاديمية للمادة"]:::teacher
            UC22["UC-22: التواصل المباشر وإرسال الملاحظات"]:::teacher
        end

        subgraph Student_UCs["حالات استخدام الطالب - Student"]
            UC23["UC-23: استعراض لوحة التحكم والجدول"]:::student
            UC24["UC-24: كشوف النتائج والشهادات والتقويم"]:::student
            UC25["UC-25: متابعة سجل الحضور والغياب"]:::student
            UC26["UC-26: أداء الكويزات والتمارين التفاعلية"]:::student
            UC27["UC-27: أداء الامتحانات الإلكترونية الشاملة"]:::student
            UC28["UC-28: استعراض تقويم الامتحانات القادمة"]:::student
            UC29["UC-29: تصفح وتحميل المذكرات والملفات"]:::student
            UC30["UC-30: التراسل الداخلي وقراءة الملاحظات"]:::student
        end

        subgraph Parent_UCs["حالات استخدام ولي الأمر - Parent"]
            UC31["UC-31: لوحة تحكم ولي الأمر ومتابعة الأبناء"]:::parent
            UC32["UC-32: استعراض النتائج والشهادات الرسمية"]:::parent
            UC33["UC-33: متابعة التطور الأكاديمي والتحليل"]:::parent
            UC34["UC-34: متابعة الانضباط وسجل الحضور"]:::parent
            UC35["UC-35: سداد الرسوم وإدارة المحفظة"]:::parent
            UC36["UC-36: تقديم ومتابعة طلبات الإجازات"]:::parent
            UC37["UC-37: التراسل المباشر وتلقي الإشعارات"]:::parent
        end
    end

    Admin["👤 مدير النظام - Admin"]:::actor
    Teacher["👤 المعلم - Teacher"]:::actor
    Student["👤 الطالب - Student"]:::actor
    Parent["👤 ولي الأمر - Parent"]:::actor

    %% Admin Connections
    Admin --- UC_Login
    Admin --- UC_Profile
    Admin --- UC01
    Admin --- UC02
    Admin --- UC03
    Admin --- UC04
    Admin --- UC05
    Admin --- UC06
    Admin --- UC07
    Admin --- UC08
    Admin --- UC09
    Admin --- UC10
    Admin --- UC11
    Admin --- UC12

    %% Teacher Connections
    Teacher --- UC_Login
    Teacher --- UC_Profile
    Teacher --- UC_Notif
    Teacher --- UC13
    Teacher --- UC14
    Teacher --- UC15
    Teacher --- UC16
    Teacher --- UC17
    Teacher --- UC18
    Teacher --- UC19
    Teacher --- UC20
    Teacher --- UC21
    Teacher --- UC22

    %% Student Connections
    Student --- UC_Login
    Student --- UC_Profile
    Student --- UC_Notif
    Student --- UC23
    Student --- UC24
    Student --- UC25
    Student --- UC26
    Student --- UC27
    Student --- UC28
    Student --- UC29
    Student --- UC30

    %% Parent Connections
    Parent --- UC_Login
    Parent --- UC_Profile
    Parent --- UC_Notif
    Parent --- UC31
    Parent --- UC32
    Parent --- UC33
    Parent --- UC34
    Parent --- UC35
    Parent --- UC36
    Parent --- UC37

    %% Includes & Extends
    UC35 -.->|include| UC09
    UC36 -.->|include| UC10
    UC27 -.->|include| UC19
    UC32 -.->|include| UC24
    UC34 -.->|include| UC25
```"""

# Save standalone mmd file
with open('docs/use_case_diagram.mmd', 'w', encoding='utf-8') as f:
    f.write(mermaid_code.replace("```mermaid\n", "").replace("\n```", "").strip())
print('docs/use_case_diagram.mmd created successfully')

# Update documentation
with open('docs/Smart_School_Documentation.md', 'r', encoding='utf-8') as f:
    content = f.read()

mermaid_section = """

### 2.5.4 كود مخطط حالات الاستخدام بلغة Mermaid

فيما يلي كود مخطط حالات الاستخدام الشامل بلغة Mermaid، والقابل للعرض والتفاعل مباشرة داخل بيئات التوثيق والبرنامج (مثل GitHub وVSCode وMarkdown Viewers):

""" + mermaid_code

if '### 2.5.4 كود مخطط حالات الاستخدام بلغة Mermaid' not in content:
    target = '# الفصل الثالث: مرحلة التصميم'
    pos = content.find(target)
    if pos != -1:
        # Find page break before Chapter 3
        page_break_pos = content.rfind('<div style="page-break-after: always;"></div>', 0, pos)
        if page_break_pos != -1:
            new_content = content[:page_break_pos] + mermaid_section + '\n\n</div>\n\n<div style="page-break-after: always;"></div>\n\n' + content[pos:]
        else:
            new_content = content[:pos] + mermaid_section + '\n\n' + content[pos:]
        with open('docs/Smart_School_Documentation.md', 'w', encoding='utf-8') as f:
            f.write(new_content)
        print('Documentation updated with Mermaid code successfully!')
    else:
        print('Could not find Chapter 3 position!')
else:
    print('Mermaid section already in documentation!')
