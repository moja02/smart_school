import html

def create_drawio_xml():
    xml = []
    xml.append('<mxGraphModel>')
    xml.append('  <root>')
    xml.append('    <mxCell id="0"/>')
    xml.append('    <mxCell id="1" parent="0"/>')
    xml.append('')
    xml.append('    <!-- ===== System Boundary ===== -->')
    xml.append('    <mxCell id="boundary" value="Smart School System — نظام المدرسة الذكية" style="swimlane;startSize=40;fillColor=#FFFFFF;strokeColor=#1A202C;strokeWidth=2;fontSize=16;fontStyle=1;align=center;" vertex="1" parent="1">')
    xml.append('      <mxGeometry x="220" y="30" width="1000" height="2350" as="geometry"/>')
    xml.append('    </mxCell>')
    xml.append('')
    
    # Actors
    xml.append('    <!-- ===== ACTORS ===== -->')
    xml.append('    <mxCell id="admin" value="Admin&#xa;مدير النظام" style="shape=mxgraph.uml.actor;whiteSpace=wrap;html=1;fillColor=#dae8fc;strokeColor=#6c8ebf;fontSize=13;fontStyle=1;verticalLabelPosition=bottom;verticalAlign=top;align=center;" vertex="1" parent="1">')
    xml.append('      <mxGeometry x="50" y="250" width="60" height="90" as="geometry"/>')
    xml.append('    </mxCell>')
    xml.append('')
    xml.append('    <mxCell id="teacher" value="Teacher&#xa;المعلم" style="shape=mxgraph.uml.actor;whiteSpace=wrap;html=1;fillColor=#d5e8d4;strokeColor=#82b366;fontSize=13;fontStyle=1;verticalLabelPosition=bottom;verticalAlign=top;align=center;" vertex="1" parent="1">')
    xml.append('      <mxGeometry x="50" y="1450" width="60" height="90" as="geometry"/>')
    xml.append('    </mxCell>')
    xml.append('')
    xml.append('    <mxCell id="student" value="Student&#xa;الطالب" style="shape=mxgraph.uml.actor;whiteSpace=wrap;html=1;fillColor=#fff2cc;strokeColor=#d6b656;fontSize=13;fontStyle=1;verticalLabelPosition=bottom;verticalAlign=top;align=center;" vertex="1" parent="1">')
    xml.append('      <mxGeometry x="1270" y="250" width="60" height="90" as="geometry"/>')
    xml.append('    </mxCell>')
    xml.append('')
    xml.append('    <mxCell id="parent" value="Parent&#xa;ولي الأمر" style="shape=mxgraph.uml.actor;whiteSpace=wrap;html=1;fillColor=#f8cecc;strokeColor=#b85450;fontSize=13;fontStyle=1;verticalLabelPosition=bottom;verticalAlign=top;align=center;" vertex="1" parent="1">')
    xml.append('      <mxGeometry x="1270" y="1450" width="60" height="90" as="geometry"/>')
    xml.append('    </mxCell>')
    xml.append('')

    # Common Use Cases
    common_ucs = [
        ("uc_login", "تسجيل الدخول&#xa;Login", 390, 60),
        ("uc_profile", "إدارة الملف الشخصي&#xa;Manage Profile", 390, 140),
        ("uc_notif", "استقبال الإشعارات الفورية&#xa;Receive Notifications", 390, 220),
    ]

    xml.append('    <!-- ===== COMMON USE CASES ===== -->')
    for ucid, text, x, y in common_ucs:
        xml.append(f'    <mxCell id="{ucid}" value="{text}" style="ellipse;whiteSpace=wrap;html=1;fillColor=#e1d5e7;strokeColor=#9673a6;fontSize=11;fontStyle=1;" vertex="1" parent="boundary">')
        xml.append(f'      <mxGeometry x="{x}" y="{y}" width="220" height="60" as="geometry"/>')
        xml.append('    </mxCell>')

    # Admin Use Cases (12)
    admin_ucs = [
        ("uc01", "UC-01: إدارة المستخدمين والأدوار&#xa;Manage Users &amp; Roles", 30, 60),
        ("uc02", "UC-02: إدارة الهيكل المدرسي والفصول&#xa;Manage School Structure", 30, 140),
        ("uc03", "UC-03: إدارة المواد وتوزيع الدرجات&#xa;Manage Subjects &amp; Grades", 30, 220),
        ("uc04", "UC-04: إسناد المواد للمعلمين&#xa;Assign Teachers", 30, 300),
        ("uc05", "UC-05: إدارة قبول الطلاب والتسكين&#xa;Manage Admissions &amp; Placement", 30, 380),
        ("uc06", "UC-06: ربط أولياء الأمور بالأبناء&#xa;Link Parents &amp; Students", 30, 460),
        ("uc07", "UC-07: إنشاء وإدارة الجداول الآلية&#xa;Generate Timetables", 30, 540),
        ("uc08", "UC-08: إدارة الامتحانات والجداول&#xa;Manage Exams &amp; Schedules", 30, 620),
        ("uc09", "UC-09: إدارة المالية والرسوم والمحافظ&#xa;Manage Fees &amp; E-Wallets", 30, 700),
        ("uc10", "UC-10: إدارة طلبات الإجازات والاستئذان&#xa;Manage Leave Requests", 30, 780),
        ("uc11", "UC-11: ترفيع الطلاب وإغلاق العام&#xa;Student Promotion &amp; Year Closure", 30, 860),
        ("uc12", "UC-12: لوحة التحكم والتقارير المتقدمة&#xa;Advanced Analytics &amp; Reports", 30, 940),
    ]

    xml.append('')
    xml.append('    <!-- ===== ADMIN USE CASES ===== -->')
    for ucid, text, x, y in admin_ucs:
        xml.append(f'    <mxCell id="{ucid}" value="{text}" style="ellipse;whiteSpace=wrap;html=1;fillColor=#dae8fc;strokeColor=#6c8ebf;fontSize=11;fontStyle=1;" vertex="1" parent="boundary">')
        xml.append(f'      <mxGeometry x="{x}" y="{y}" width="250" height="60" as="geometry"/>')
        xml.append('    </mxCell>')

    # Teacher Use Cases (10)
    teacher_ucs = [
        ("uc13", "UC-13: استعراض الجدول الأسبوعي&#xa;View Weekly Schedule", 30, 1100),
        ("uc14", "UC-14: تسجيل الحضور والغياب اليومي&#xa;Record Attendance", 30, 1180),
        ("uc15", "UC-15: إدارة التقييمات وأعمال السنة&#xa;Manage Assessments", 30, 1260),
        ("uc16", "UC-16: إدخال واعتماد الدرجات النهائية&#xa;Record Final Marks", 30, 1340),
        ("uc17", "UC-17: إدارة الدروس والواجبات المنزلية&#xa;Manage Lessons &amp; Homeworks", 30, 1420),
        ("uc18", "UC-18: إدارة الكويزات وبنك الأسئلة&#xa;Manage Question Bank &amp; Quizzes", 30, 1500),
        ("uc19", "UC-19: إنشاء وإدارة الامتحانات الإلكترونية&#xa;Manage Online Exams", 30, 1580),
        ("uc20", "UC-20: إدارة المذكرات والملفات الدراسية&#xa;Manage Study Materials", 30, 1660),
        ("uc21", "UC-21: استخراج التقارير الأكاديمية للمادة&#xa;Subject Academic Reports", 30, 1740),
        ("uc22", "UC-22: التواصل المباشر وإرسال الملاحظات&#xa;Messaging &amp; Student Notes", 30, 1820),
    ]

    xml.append('')
    xml.append('    <!-- ===== TEACHER USE CASES ===== -->')
    for ucid, text, x, y in teacher_ucs:
        xml.append(f'    <mxCell id="{ucid}" value="{text}" style="ellipse;whiteSpace=wrap;html=1;fillColor=#d5e8d4;strokeColor=#82b366;fontSize=11;fontStyle=1;" vertex="1" parent="boundary">')
        xml.append(f'      <mxGeometry x="{x}" y="{y}" width="250" height="60" as="geometry"/>')
        xml.append('    </mxCell>')

    # Student Use Cases (8)
    student_ucs = [
        ("uc23", "UC-23: استعراض لوحة التحكم والجدول&#xa;View Schedule &amp; Dashboard", 720, 60),
        ("uc24", "UC-24: كشوف النتائج والشهادات والتقويم&#xa;View Report Cards &amp; Grades", 720, 140),
        ("uc25", "UC-25: متابعة سجل الحضور والغياب&#xa;Track Attendance", 720, 220),
        ("uc26", "UC-26: أداء الكويزات والتمارين التفاعلية&#xa;Take Interactive Quizzes", 720, 300),
        ("uc27", "UC-27: أداء الامتحانات الإلكترونية الشاملة&#xa;Take Online Exams", 720, 380),
        ("uc28", "UC-28: استعراض تقويم الامتحانات القادمة&#xa;View Exam Calendar", 720, 460),
        ("uc29", "UC-29: تصفح وتحميل المذكرات والملفات&#xa;Download Study Materials", 720, 540),
        ("uc30", "UC-30: التراسل الداخلي وقراءة الملاحظات&#xa;Read Messages &amp; Notes", 720, 620),
    ]

    xml.append('')
    xml.append('    <!-- ===== STUDENT USE CASES ===== -->')
    for ucid, text, x, y in student_ucs:
        xml.append(f'    <mxCell id="{ucid}" value="{text}" style="ellipse;whiteSpace=wrap;html=1;fillColor=#fff2cc;strokeColor=#d6b656;fontSize=11;fontStyle=1;" vertex="1" parent="boundary">')
        xml.append(f'      <mxGeometry x="{x}" y="{y}" width="250" height="60" as="geometry"/>')
        xml.append('    </mxCell>')

    # Parent Use Cases (7)
    parent_ucs = [
        ("uc31", "UC-31: لوحة تحكم ولي الأمر ومتابعة الأبناء&#xa;Monitor Children Overview", 720, 1100),
        ("uc32", "UC-32: استعراض النتائج والشهادات الرسمية&#xa;View Children Certificates", 720, 1180),
        ("uc33", "UC-33: متابعة التطور الأكاديمي والتحليل&#xa;Academic Progress Analytics", 720, 1260),
        ("uc34", "UC-34: متابعة الانضباط وسجل الحضور&#xa;Discipline &amp; Attendance Summary", 720, 1340),
        ("uc35", "UC-35: سداد الرسوم وإدارة المحفظة&#xa;Pay Fees &amp; Manage E-Wallet", 720, 1420),
        ("uc36", "UC-36: تقديم ومتابعة طلبات الإجازات&#xa;Submit Leave Requests", 720, 1500),
        ("uc37", "UC-37: التراسل المباشر وتلقي الإشعارات&#xa;Direct Messaging &amp; Alerts", 720, 1580),
    ]

    xml.append('')
    xml.append('    <!-- ===== PARENT USE CASES ===== -->')
    for ucid, text, x, y in parent_ucs:
        xml.append(f'    <mxCell id="{ucid}" value="{text}" style="ellipse;whiteSpace=wrap;html=1;fillColor=#f8cecc;strokeColor=#b85450;fontSize=11;fontStyle=1;" vertex="1" parent="boundary">')
        xml.append(f'      <mxGeometry x="{x}" y="{y}" width="250" height="60" as="geometry"/>')
        xml.append('    </mxCell>')

    # Edges / Links
    xml.append('')
    xml.append('    <!-- ===== EDGES / ASSOCIATIONS ===== -->')
    edge_id = 100

    def add_edge(source, target):
        nonlocal edge_id
        edge_id += 1
        xml.append(f'    <mxCell id="e_{edge_id}" style="edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeColor=#4A5568;endArrow=none;" edge="1" parent="1" source="{source}" target="{target}">')
        xml.append('      <mxGeometry relative="1" as="geometry"/>')
        xml.append('    </mxCell>')

    # Admin Edges
    add_edge("admin", "uc_login")
    add_edge("admin", "uc_profile")
    for ucid, _, _, _ in admin_ucs:
        add_edge("admin", ucid)

    # Teacher Edges
    add_edge("teacher", "uc_login")
    add_edge("teacher", "uc_profile")
    add_edge("teacher", "uc_notif")
    for ucid, _, _, _ in teacher_ucs:
        add_edge("teacher", ucid)

    # Student Edges
    add_edge("student", "uc_login")
    add_edge("student", "uc_profile")
    add_edge("student", "uc_notif")
    for ucid, _, _, _ in student_ucs:
        add_edge("student", ucid)

    # Parent Edges
    add_edge("parent", "uc_login")
    add_edge("parent", "uc_profile")
    add_edge("parent", "uc_notif")
    for ucid, _, _, _ in parent_ucs:
        add_edge("parent", ucid)

    xml.append('  </root>')
    xml.append('</mxGraphModel>')

    return '\n'.join(xml)

drawio_content = create_drawio_xml()
with open('docs/use_case_diagram.drawio', 'w', encoding='utf-8') as f:
    f.write(drawio_content)

print("Draw.io XML generated successfully! Length:", len(drawio_content))
