import xml.etree.ElementTree as ET

def build_plain_drawio():
    mxGraphModel = ET.Element('mxGraphModel')
    root = ET.SubElement(mxGraphModel, 'root')
    
    ET.SubElement(root, 'mxCell', id="0")
    ET.SubElement(root, 'mxCell', id="1", parent="0")

    # Boundary
    boundary = ET.SubElement(root, 'mxCell',
        id="boundary",
        value="Smart School System — نظام المدرسة الذكية",
        style="swimlane;startSize=40;fillColor=#ffffff;strokeColor=#000000;strokeWidth=1;fontSize=14;fontStyle=1;align=center;",
        vertex="1", parent="1"
    )
    ET.SubElement(boundary, 'mxGeometry', attrib={'x': "200", 'y': "30", 'width': "950", 'height': "2400", 'as': "geometry"})

    # Actors
    actors_data = [
        ("admin", "مدير النظام\n(Admin)", "50", "250"),
        ("teacher", "المعلم\n(Teacher)", "50", "1450"),
        ("student", "الطالب\n(Student)", "1200", "250"),
        ("parent", "ولي الأمر\n(Parent)", "1200", "1450"),
    ]

    for act_id, act_val, x, y in actors_data:
        cell = ET.SubElement(root, 'mxCell',
            id=act_id,
            value=act_val,
            style="shape=mxgraph.uml.actor;whiteSpace=wrap;html=1;fillColor=#ffffff;strokeColor=#000000;fontSize=12;fontStyle=1;verticalLabelPosition=bottom;verticalAlign=top;align=center;",
            vertex="1", parent="1"
        )
        ET.SubElement(cell, 'mxGeometry', attrib={'x': str(x), 'y': str(y), 'width': "60", 'height': "90", 'as': "geometry"})

    # Use Cases
    common_ucs = [
        ("uc_login", "تسجيل الدخول\nLogin", 360, 60),
        ("uc_profile", "إدارة الملف الشخصي\nManage Profile", 360, 140),
        ("uc_notif", "استقبال الإشعارات الفورية\nNotifications", 360, 220),
    ]

    admin_ucs = [
        ("uc01", "UC-01: إدارة المستخدمين والأدوار", 30, 60),
        ("uc02", "UC-02: إدارة الهيكل المدرسي والفصول", 30, 140),
        ("uc03", "UC-03: إدارة المواد وتوزيع الدرجات", 30, 220),
        ("uc04", "UC-04: إسناد المواد للمعلمين", 30, 300),
        ("uc05", "UC-05: إدارة قبول الطلاب والتسكين", 30, 380),
        ("uc06", "UC-06: ربط أولياء الأمور بالأبناء", 30, 460),
        ("uc07", "UC-07: إنشاء وإدارة الجداول الآلية", 30, 540),
        ("uc08", "UC-08: إدارة الامتحانات والجداول", 30, 620),
        ("uc09", "UC-09: إدارة المالية والرسوم والمافظ", 30, 700),
        ("uc10", "UC-10: إدارة طلبات الإجازات والاستئذان", 30, 780),
        ("uc11", "UC-11: ترفيع الطلاب وإغلاق العام", 30, 860),
        ("uc12", "UC-12: لوحة التحكم والتقارير المتقدمة", 30, 940),
    ]

    teacher_ucs = [
        ("uc13", "UC-13: استعراض الجدول الأسبوعي", 30, 1100),
        ("uc14", "UC-14: تسجيل الحضور والغياب اليومي", 30, 1180),
        ("uc15", "UC-15: إدارة التقييمات وأعمال السنة", 30, 1260),
        ("uc16", "UC-16: إدخال واعتماد الدرجات النهائية", 30, 1340),
        ("uc17", "UC-17: إدارة الدروس والواجبات المنزلية", 30, 1420),
        ("uc18", "UC-18: إدارة الكويزات وبنك الأسئلة", 30, 1500),
        ("uc19", "UC-19: إنشاء وإدارة الامتحانات الإلكترونية", 30, 1580),
        ("uc20", "UC-20: إدارة المذكرات والملفات الدراسية", 30, 1660),
        ("uc21", "UC-21: استخراج التقارير الأكاديمية للمادة", 30, 1740),
        ("uc22", "UC-22: التواصل المباشر وإرسال الملاحظات", 30, 1820),
    ]

    student_ucs = [
        ("uc23", "UC-23: استعراض لوحة التحكم والجدول", 670, 60),
        ("uc24", "UC-24: كشوف النتائج والشهادات والتقويم", 670, 140),
        ("uc25", "UC-25: متابعة سجل الحضور والغياب", 670, 220),
        ("uc26", "UC-26: أداء الكويزات والتمارين التفاعلية", 670, 300),
        ("uc27", "UC-27: أداء الامتحانات الإلكترونية الشاملة", 670, 380),
        ("uc28", "UC-28: استعراض تقويم الامتحانات القادمة", 670, 460),
        ("uc29", "UC-29: تصفح وتحميل المذكرات والملفات", 670, 540),
        ("uc30", "UC-30: التراسل الداخلي وقراءة الملاحظات", 670, 620),
    ]

    parent_ucs = [
        ("uc31", "UC-31: لوحة تحكم ولي الأمر ومتابعة الأبناء", 670, 1100),
        ("uc32", "UC-32: استعراض النتائج والشهادات الرسمية", 670, 1180),
        ("uc33", "UC-33: متابعة التطور الأكاديمي والتحليل", 670, 1260),
        ("uc34", "UC-34: متابعة الانضباط وسجل الحضور", 670, 1340),
        ("uc35", "UC-35: سداد الرسوم وإدارة المحفظة", 670, 1420),
        ("uc36", "UC-36: تقديم ومتابعة طلبات الإجازات", 670, 1500),
        ("uc37", "UC-37: التراسل المباشر وتلقي الإشعارات", 670, 1580),
    ]

    all_ucs = common_ucs + admin_ucs + teacher_ucs + student_ucs + parent_ucs

    for u_id, u_val, x, y in all_ucs:
        cell = ET.SubElement(root, 'mxCell',
            id=u_id,
            value=u_val,
            style="ellipse;whiteSpace=wrap;html=1;fillColor=#ffffff;strokeColor=#000000;fontSize=11;fontStyle=0;",
            vertex="1", parent="boundary"
        )
        width = "240" if u_id.startswith("uc") else "220"
        ET.SubElement(cell, 'mxGeometry', attrib={'x': str(x), 'y': str(y), 'width': width, 'height': "60", 'as': "geometry"})

    # Connectors helper
    edge_count = 0
    def add_edge(src, tgt):
        nonlocal edge_count
        edge_count += 1
        edge = ET.SubElement(root, 'mxCell',
            id=f"e_{edge_count}",
            style="edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeColor=#000000;endArrow=none;",
            edge="1", parent="1", source=src, target=tgt
        )
        ET.SubElement(edge, 'mxGeometry', attrib={'relative': "1", 'as': "geometry"})

    # Admin edges
    for target in ["uc_login", "uc_profile"] + [f"uc{i:02d}" for i in range(1, 13)]:
        add_edge("admin", target)

    # Teacher edges
    for target in ["uc_login", "uc_profile", "uc_notif"] + [f"uc{i:02d}" for i in range(13, 23)]:
        add_edge("teacher", target)

    # Student edges
    for target in ["uc_login", "uc_profile", "uc_notif"] + [f"uc{i:02d}" for i in range(23, 31)]:
        add_edge("student", target)

    # Parent edges
    for target in ["uc_login", "uc_profile", "uc_notif"] + [f"uc{i:02d}" for i in range(31, 38)]:
        add_edge("parent", target)

    tree = ET.ElementTree(mxGraphModel)
    ET.indent(tree, space="  ")
    tree.write("docs/use_case_diagram_plain.drawio", encoding="utf-8", xml_declaration=False)
    print("docs/use_case_diagram_plain.drawio created successfully")

build_plain_drawio()
