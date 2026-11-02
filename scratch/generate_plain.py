import urllib.request
import json
import ssl

ctx = ssl._create_unverified_context()

puml_code = """@startuml
left to right direction

actor "مدير النظام (Admin)" as Admin
actor "المعلم (Teacher)" as Teacher
actor "الطالب (Student)" as Student
actor "ولي الأمر (Parent)" as Parent

rectangle "نظام المدرسة الذكية (Smart School System)" {

  usecase "تسجيل الدخول" as UC_Login
  usecase "إدارة الملف الشخصي" as UC_Profile
  usecase "استقبال الإشعارات الفورية" as UC_Notif

  usecase "UC-01: إدارة المستخدمين والأدوار" as UC01
  usecase "UC-02: إدارة الهيكل المدرسي والفصول" as UC02
  usecase "UC-03: إدارة المواد وتوزيع الدرجات" as UC03
  usecase "UC-04: إسناد المواد للمعلمين" as UC04
  usecase "UC-05: إدارة قبول الطلاب والتسكين" as UC05
  usecase "UC-06: ربط أولياء الأمور بالأبناء" as UC06
  usecase "UC-07: إنشاء وإدارة الجداول الآلية" as UC07
  usecase "UC-08: إدارة الامتحانات والجداول" as UC08
  usecase "UC-09: إدارة المالية والرسوم والمحافظ" as UC09
  usecase "UC-10: إدارة طلبات الإجازات والاستئذان" as UC10
  usecase "UC-11: ترفيع الطلاب وإغلاق العام" as UC11
  usecase "UC-12: لوحة التحكم والتقارير المتقدمة" as UC12

  usecase "UC-13: استعراض الجدول الأسبوعي" as UC13
  usecase "UC-14: تسجيل الحضور والغياب اليومي" as UC14
  usecase "UC-15: إدارة التقييمات وأعمال السنة" as UC15
  usecase "UC-16: إدخال واعتماد الدرجات النهائية" as UC16
  usecase "UC-17: إدارة الدروس والواجبات المنزلية" as UC17
  usecase "UC-18: إدارة الكويزات وبنك الأسئلة" as UC18
  usecase "UC-19: إنشاء وإدارة الامتحانات الإلكترونية" as UC19
  usecase "UC-20: إدارة المذكرات والملفات الدراسية" as UC20
  usecase "UC-21: استخراج التقارير الأكاديمية للمادة" as UC21
  usecase "UC-22: التواصل المباشر وإرسال الملاحظات" as UC22

  usecase "UC-23: استعراض لوحة التحكم والجدول" as UC23
  usecase "UC-24: كشوف النتائج والشهادات والتقويم" as UC24
  usecase "UC-25: متابعة سجل الحضور والغياب" as UC25
  usecase "UC-26: أداء الكويزات والتمارين التفاعلية" as UC26
  usecase "UC-27: أداء الامتحانات الإلكترونية الشاملة" as UC27
  usecase "UC-28: استعراض تقويم الامتحانات القادمة" as UC28
  usecase "UC-29: تصفح وتحميل المذكرات والملفات" as UC29
  usecase "UC-30: التراسل الداخلي وقراءة الملاحظات" as UC30

  usecase "UC-31: لوحة تحكم ولي الأمر ومتابعة الأبناء" as UC31
  usecase "UC-32: استعراض النتائج والشهادات الرسمية" as UC32
  usecase "UC-33: متابعة التطور الأكاديمي والتحليل" as UC33
  usecase "UC-34: متابعة الانضباط وسجل الحضور" as UC34
  usecase "UC-35: سداد الرسوم وإدارة المحفظة" as UC35
  usecase "UC-36: تقديم ومتابعة طلبات الإجازات" as UC36
  usecase "UC-37: التراسل المباشر وتلقي الإشعارات" as UC37
}

Admin --> UC_Login
Admin --> UC_Profile
Admin --> UC01
Admin --> UC02
Admin --> UC03
Admin --> UC04
Admin --> UC05
Admin --> UC06
Admin --> UC07
Admin --> UC08
Admin --> UC09
Admin --> UC10
Admin --> UC11
Admin --> UC12

Teacher --> UC_Login
Teacher --> UC_Profile
Teacher --> UC_Notif
Teacher --> UC13
Teacher --> UC14
Teacher --> UC15
Teacher --> UC16
Teacher --> UC17
Teacher --> UC18
Teacher --> UC19
Teacher --> UC20
Teacher --> UC21
Teacher --> UC22

Student --> UC_Login
Student --> UC_Profile
Student --> UC_Notif
Student --> UC23
Student --> UC24
Student --> UC25
Student --> UC26
Student --> UC27
Student --> UC28
Student --> UC29
Student --> UC30

Parent --> UC_Login
Parent --> UC_Profile
Parent --> UC_Notif
Parent --> UC31
Parent --> UC32
Parent --> UC33
Parent --> UC34
Parent --> UC35
Parent --> UC36
Parent --> UC37

UC35 ..> UC09 : <<include>>
UC36 ..> UC10 : <<include>>
UC27 ..> UC19 : <<include>>
UC32 ..> UC24 : <<include>>
UC34 ..> UC25 : <<include>>

@enduml"""

with open('docs/use_case_diagram_plain.puml', 'w', encoding='utf-8') as f:
    f.write(puml_code)

req = urllib.request.Request(
    'https://kroki.io/plantuml/png',
    data=json.dumps({'diagram_source': puml_code, 'output_format': 'png'}).encode('utf-8'),
    headers={
        'Content-Type': 'application/json',
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
    }
)

try:
    with urllib.request.urlopen(req, context=ctx) as resp:
        with open('docs/use_case_diagram_plain.png', 'wb') as out:
            out.write(resp.read())
    print('Plain PNG generated successfully via Kroki JSON!')
except Exception as e:
    print('Error generating PNG:', e)
