import re

with open('docs/use_case_diagram.mmd', 'r', encoding='utf-8') as f:
    mermaid_code = f.read()

html_content = f"""<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart School - Use Case Diagram (مخطط حالات الاستخدام)</title>
    <script src="https://cdn.jsdelivr.net/npm/mermaid/dist/mermaid.min.js"></script>
    <style>
        body {{
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 20px;
            text-align: center;
        }}
        .card {{
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 25px;
            max-width: 1400px;
            margin: 0 auto;
        }}
        h1 {{
            color: #1a365d;
            margin-bottom: 5px;
        }}
        p {{
            color: #4a5568;
            font-size: 14px;
            margin-bottom: 20px;
        }}
        .btn {{
            background-color: #2b6cb0;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            margin-bottom: 20px;
            transition: background 0.3s;
        }}
        .btn:hover {{
            background-color: #2c5282;
        }}
        .mermaid {{
            display: flex;
            justify-content: center;
            overflow-x: auto;
        }}
    </style>
</head>
<body>

<div class="card">
    <h1>مخطط حالات الاستخدام — نظام المدرسة الذكية (Smart School)</h1>
    <p>يمكنك حفظ هذا المخطط كصورة عالية الدقة لنسخها وإدراجها مباشرة في مستند الوورد (Microsoft Word).</p>
    <button class="btn" onclick="downloadSVG()">تحميل المخطط كصورة SVG / PNG للوورد</button>

    <div class="mermaid" id="diagram">
{mermaid_code}
    </div>
</div>

<script>
    mermaid.initialize({{ startOnLoad: true, theme: 'neutral' }});

    function downloadSVG() {{
        const svg = document.querySelector('.mermaid svg');
        if (!svg) {{
            alert('انتظر تحميل المخطط أولاً');
            return;
        }}
        const svgData = new XMLSerializer().serializeToString(svg);
        const svgBlob = new Blob([svgData], {{ type: 'image/svg+xml;charset=utf-8' }});
        const svgUrl = URL.createObjectURL(svgBlob);
        const downloadLink = document.createElement('a');
        downloadLink.href = svgUrl;
        downloadLink.download = 'Smart_School_Use_Case_Diagram.svg';
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }}
</script>

</body>
</html>
"""

with open('docs/use_case_diagram.html', 'w', encoding='utf-8') as f:
    f.write(html_content)

print('docs/use_case_diagram.html created successfully')
