<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Size and Position</title>
    <style>
        #pdf-container {
            position: relative;
        }

        #pdf-info {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background-color: white;
            padding: 10px;
            border: 1px solid black;
        }
    </style>
</head>
<body>
    <div id="pdf-container">
        <iframe id="pdf" src="<?= base_url();?>uploads/sample-calendar/january-front.jpg" width="100%" height="500px"></iframe>
        <div id="pdf-info"></div>
    </div>

    <script>
        const pdf = document.getElementById('pdf');
        const pdfInfo = document.getElementById('pdf-info');

        pdf.onload = function() {
            const pdfWidth = pdf.contentWindow.document.documentElement.scrollWidth;
            const pdfHeight = pdf.contentWindow.document.documentElement.scrollHeight;

            pdfInfo.textContent = `PDF Size: ${pdfWidth}px x ${pdfHeight}px`;
        };
    </script>
</body>
</html>
