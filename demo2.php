<!DOCTYPE html>
<html>
<head>
    <title>Menu Image Uploader</title>
    <style>
        body {
            background-color: #333;
            color: #f9f9f9;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        #imageUpload, #uploadButton {
            margin: 20px;
            padding: 10px;
            font-size: 1.2em;
            background-color: #f9f9f9;
            color: #333;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .imagePreview {
            max-width: 100px;
            max-height: 100px;
            border: 2px solid #f9f9f9;
            border-radius: 5px;
            object-fit: cover;
            margin: 10px;
        }
        #uploadButton:hover {
            background-color: #ffc107;
            color: #333;
        }
        .jsonKey {
            font-weight: bold;
        }
        .jsonValue {
            margin: 10px 0;
            width: 100%;
            padding: 5px;
        }
        #outputTable {
            margin-top: 30px;
            width: 100%;
            border-collapse: collapse;
        }
        .outputRow {
            display: flex;
            justify-content: space-between;
            border: 1px solid #f9f9f9;
            margin-bottom: 30px;
            padding: 20px;
            align-items: center;
        }
        .outputImage {
            max-width: 200px;
            max-height: 200px;
            border-radius: 5px;
            object-fit: cover;
            transition: transform 0.3s ease-in-out;
        }
        .outputImage:hover {
            transform: scale(1.1);
        }
        .outputJSON {
            max-height: 200px;
            width: 300px;
            overflow-y: auto;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
        }
        .downloadButtonContainer {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .downloadButton {
            margin: 0 10px;
            padding: 10px;
            background-color: #f9f9f9;
            color: #333;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .downloadButton:hover {
            background-color: #ffc107;
            color: #333;
        }
        .individualDownloadButton {
            padding: 5px;
            background-color: #f9f9f9;
            color: #333;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .individualDownloadButton:hover {
            background-color: #ffc107;
            color: #333;
        }
    </style>
</head>
<body>
    <h1>Menu Image Uploader</h1>
    <input type="file" id="imageUpload" accept="image/*" multiple>
    <div id="imagePreviews"></div>
    <button id="uploadButton">Upload</button>
    <div id="outputTable"></div>
    <div class="downloadButtonContainer">
        <button id="downloadCSV" class="downloadButton">Download CSV</button>
        <button id="downloadJSON" class="downloadButton">Download JSON</button>
    </div>

    <script>
        document.getElementById('imageUpload').addEventListener('change', function(e) {
            const files = Array.from(e.target.files).slice(0, 10);
            const previewContainer = document.getElementById('imagePreviews');
            previewContainer.innerHTML = '';  // Clear previous previews

            for (let file of files) {
                const img = document.createElement('img');
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                    img.className = 'imagePreview';
                    previewContainer.appendChild(img);
                };
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('uploadButton').addEventListener('click', async function() {
            const files = Array.from(document.getElementById('imageUpload').files).slice(0, 10);
            if (files.length === 0) return;

            const outputTable = document.getElementById('outputTable');
            outputTable.innerHTML = '';  // Clear previous output

            let serialNumber = 1;

            for (let file of files) {
                const formData = new FormData();
                formData.append('file', file);

                const response = await fetch('http://3.112.206.137/pizza', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                const outputRow = document.createElement('div');
                outputRow.className = 'outputRow';

                const serialNumberDiv = document.createElement('div');
                serialNumberDiv.textContent = '#' + serialNumber++;
                outputRow.appendChild(serialNumberDiv);

                if (result.img_url) {
                    const img = document.createElement('img');
                    img.src = result.img_url;
                    img.className = 'outputImage';
                    img.addEventListener('click', function() {
                        const popupImg = document.createElement('img');
                        popupImg.src = result.img_url;
                        popupImg.style.width = '1500px';
                        popupImg.style.height = '1500px';
                        window.open('', '_blank').document.body.appendChild(popupImg);
                    });
                    outputRow.appendChild(img);
                }

                const jsonOutput = document.createElement('div');
                jsonOutput.className = 'outputJSON';
                for (let key in result) {
                    const keyElement = document.createElement('div');
                    keyElement.textContent = key;
                    keyElement.className = 'jsonKey';

                    const valueElement = document.createElement('input');
                    valueElement.value = result[key];
                    valueElement.className = 'jsonValue';

                    jsonOutput.appendChild(keyElement);
                    jsonOutput.appendChild(valueElement);
                }
                outputRow.appendChild(jsonOutput);

                const downloadButtonContainer = document.createElement('div');
                downloadButtonContainer.className = 'downloadButtonContainer';

                const downloadCSV = document.createElement('button');
                downloadCSV.textContent = 'Download CSV';
                downloadCSV.className = 'individualDownloadButton';
                downloadCSV.addEventListener('click', function() {
                    const headers = Array.from(outputTable.querySelectorAll('.jsonKey')).map(element => element.textContent);
                    const values = Array.from(outputRow.querySelectorAll('.jsonValue')).map(element => element.value);
                    const csvContent = [headers.join(','), values.join(',')].join('\n');

                    const link = document.createElement('a');
                    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                    link.href = URL.createObjectURL(blob);
                    link.download = serialNumber + '.csv';
                    link.click();
                });
                downloadButtonContainer.appendChild(downloadCSV);

                const downloadJSON = document.createElement('button');
                downloadJSON.textContent = 'Download JSON';
                downloadJSON.className = 'individualDownloadButton';
                downloadJSON.addEventListener('click', function() {
                    const outputData = {};
                    const keyElements = Array.from(outputRow.querySelectorAll('.jsonKey'));
                    const valueElements = Array.from(outputRow.querySelectorAll('.jsonValue'));

                    keyElements.forEach((keyElement, index) => {
                        outputData[keyElement.textContent] = valueElements[index].value;
                    });

                    const jsonContent = JSON.stringify(outputData, null, 2);

                    const link = document.createElement('a');
                    const blob = new Blob([jsonContent], { type: 'application/json' });
                    link.href = URL.createObjectURL(blob);
                    link.download = serialNumber + '.json';
                    link.click();
                });
                downloadButtonContainer.appendChild(downloadJSON);

                outputRow.appendChild(downloadButtonContainer);
                outputTable.appendChild(outputRow);
            }
        });

        document.getElementById('downloadCSV').addEventListener('click', function() {
            const tableRows = Array.from(document.querySelectorAll('.outputRow'));
            const headers = Array.from(tableRows[0].querySelectorAll('.jsonKey')).map(element => element.textContent);
            const rows = tableRows.map(row => Array.from(row.querySelectorAll('.jsonValue')).map(element => element.value));

            const csvContent = [headers.join(',')].concat(rows.map(row => row.join(','))).join('\n');

            const link = document.createElement('a');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            link.href = URL.createObjectURL(blob);
            link.download = 'output.csv';
            link.click();
        });

        document.getElementById('downloadJSON').addEventListener('click', function() {
            const tableRows = Array.from(document.querySelectorAll('.outputRow'));
            const outputData = tableRows.map(row => {
                const rowData = {};
                const keyElements = Array.from(row.querySelectorAll('.jsonKey'));
                const valueElements = Array.from(row.querySelectorAll('.jsonValue'));

                keyElements.forEach((keyElement, index) => {
                    rowData[keyElement.textContent] = valueElements[index].value;
                });

                return rowData;
            });

            const jsonContent = JSON.stringify(outputData, null, 2);

            const link = document.createElement('a');
            const blob = new Blob([jsonContent], { type: 'application/json' });
            link.href = URL.createObjectURL(blob);
            link.download = 'output.json';
            link.click();
        });
    </script>
</body>
</html>
