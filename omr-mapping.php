<?php require_once 'header.php'; ?>

<style>
    body {
        overflow-x: hidden;
        font-family: Arial;
    }

    .image-wrapper {
        width: 100%;
        height: 80vh;
        border: 1px solid #dcdcdc;
        overflow: hidden;
        position: relative;
        background: #f8f8f8;
        cursor: crosshair;
    }

    #targetImageomr {
        position: absolute;
        top: 0;
        left: 0;
        transform-origin: top left;
        user-select: none;
        -webkit-user-drag: none;
        max-width: none;
    }

    .coord-box {
        margin-bottom: 12px;
    }

    .active-select {
        border: 2px solid #0d6efd !important;
        background: #eef5ff;
    }

    .marker {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        position: absolute;
        transform: translate(-50%, -50%);
        pointer-events: none;
        z-index: 9999;
    }

    .marker.red {
        background: red;
    }

    .marker.green {
        background: limegreen;
    }

    textarea {
        font-size: 12px;
    }
</style>

<div class="container-xxl container-p-y">
    <div class="row">

        <!-- LEFT -->
        <div class="col-md-8">

            <input type="text" class="form-control mb-3" id="omrName" placeholder="Insert OMR Name Here...">

            <input type="file" class="form-control mb-3" id="imageInput">

            <div class="image-wrapper" id="imageWrapperomr">
                <img id="targetImageomr">
            </div>

        </div>

        <!-- RIGHT -->
        <div class="col-md-4">

            <div class="card">
                <div class="card-body">

                    <h5>Coordinate Settings</h5>

                    <!-- TOP LEFT -->
                    <div class="row mb-2 align-items-end">
                        <div class="col-4">
                            <button class="btn btn-primary w-100 active-select" id="selectTopLeft">
                                TL
                            </button>
                        </div>
                        <div class="col-4">
                            <input class="form-control" id="topLeftX" placeholder="X">
                        </div>
                        <div class="col-4">
                            <input class="form-control" id="topLeftY" placeholder="Y">
                        </div>
                    </div>

                    <!-- BOTTOM RIGHT -->
                    <div class="row mb-2 align-items-end">
                        <div class="col-4">
                            <button class="btn btn-success w-100" id="selectBottomRight">
                                BR
                            </button>
                        </div>
                        <div class="col-4">
                            <input class="form-control" id="bottomRightX" placeholder="X">
                        </div>
                        <div class="col-4">
                            <input class="form-control" id="bottomRightY" placeholder="Y">
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-2">
                        <div class="col-6">
                            <input class="form-control" id="xGrid" placeholder="X Grid">
                        </div>
                        <div class="col-6">
                            <input class="form-control" id="yGrid" placeholder="Y Grid">
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-6">
                            <select class="form-select" id="dataCategory">
                                <option value="Question">Question</option>
                                <option value="OMR_data">OMR_data</option>
                            </select>
                        </div>

                        <div class="col-6">
                            <select class="form-select" id="dataType">
                                <option value="Roll">Roll</option>
                                <option value="Subject">Subject</option>
                                <option value="Setcode">Setcode</option>
                            </select>
                        </div>
                    </div>

                    <button class="btn btn-dark w-100" id="generateJson">
                        Generate JSON
                    </button>

                    <textarea class="form-control mt-3" rows="5" id="jsonOutput"></textarea>

                    <textarea class="form-control mt-3" rows="5" id="jsonMain"></textarea>
                    <textarea class="form-control mt-3" rows="5" id="jsonRoll"></textarea>
                    <textarea class="form-control mt-3" rows="5" id="jsonSubject"></textarea>
                    <textarea class="form-control mt-3" rows="5" id="jsonSet"></textarea>
                    <textarea class="form-control mt-3" rows="5" id="jsonQuestion"></textarea>

                    <button class="btn btn-dark w-100" id="generateJsonFinal">
                        Generate Final JSON
                    </button>

                </div>
            </div>

        </div>

    </div>
</div>

<?php require_once 'footer.php'; ?>


<script>

    let scale = 1;
    let posX = 0;
    let posY = 0;

    let isDragging = false;
    let startX = 0;
    let startY = 0;

    let currentMode = 'topLeft';

    const image = document.getElementById('targetImageomr');
    const wrapper = document.getElementById('imageWrapperomr');

    const topLeftBtn = document.getElementById('selectTopLeft');
    const bottomRightBtn = document.getElementById('selectBottomRight');

    const topLeftX = document.getElementById('topLeftX');
    const topLeftY = document.getElementById('topLeftY');
    const bottomRightX = document.getElementById('bottomRightX');
    const bottomRightY = document.getElementById('bottomRightY');


    // ---------------- IMAGE LOAD ----------------
    document.getElementById('imageInput').addEventListener('change', e => {

        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();

        reader.onload = ev => {
            image.src = ev.target.result;
            scale = 1;
            posX = 0;
            posY = 0;
            updateTransform();
        };

        reader.readAsDataURL(file);
    });


    // ---------------- ZOOM ----------------
    wrapper.addEventListener('wheel', e => {
        e.preventDefault();

        scale += (e.deltaY < 0) ? 0.1 : -0.1;
        if (scale < 0.2) scale = 0.2;

        updateTransform();
    });


    // ---------------- PAN ----------------
    wrapper.addEventListener('mousedown', e => {
        isDragging = true;
        startX = e.clientX - posX;
        startY = e.clientY - posY;
    });

    document.addEventListener('mouseup', () => isDragging = false);

    document.addEventListener('mousemove', e => {
        if (!isDragging) return;

        posX = e.clientX - startX;
        posY = e.clientY - startY;

        updateTransform();
    });


    // ---------------- TRANSFORM ----------------
    function updateTransform() {
        image.style.transform =
            `translate(${posX}px, ${posY}px) scale(${scale})`;
    }


    // ---------------- MODE ----------------
    topLeftBtn.onclick = () => {
        currentMode = 'topLeft';
    };

    bottomRightBtn.onclick = () => {
        currentMode = 'bottomRight';
    };


    // ---------------- CLICK (0–1 COORD) ----------------
    wrapper.addEventListener('click', e => {

        if (isDragging || !image.src) return;

        const rect = image.getBoundingClientRect();

        const x = (e.clientX - rect.left) / rect.width;
        const y = (e.clientY - rect.top) / rect.height;

        if (x < 0 || y < 0 || x > 1 || y > 1) return;

        if (currentMode === 'topLeft') {

            topLeftX.value = x.toFixed(16);
            topLeftY.value = y.toFixed(16);

            drawMarker(x, y, 'red');

        } else {

            bottomRightX.value = x.toFixed(16);
            bottomRightY.value = y.toFixed(16);

            drawMarker(x, y, 'green');
        }
    });


    // ---------------- MARKER ----------------
    function drawMarker(x, y, color) {

        const old = document.querySelector('.marker.' + color);
        if (old) old.remove();

        const marker = document.createElement('div');
        marker.className = 'marker ' + color;

        const rect = image.getBoundingClientRect();

        marker.style.left = (rect.width * x * scale + posX) + 'px';
        marker.style.top = (rect.height * y * scale + posY) + 'px';

        wrapper.appendChild(marker);
    }


    // ---------------- JSON GENERATOR ----------------
    document.getElementById('generateJson').onclick = () => {

        const x1 = parseFloat(topLeftX.value);
        const y1 = parseFloat(topLeftY.value);
        const x2 = parseFloat(bottomRightX.value);
        const y2 = parseFloat(bottomRightY.value);

        const xGrid = parseInt(document.getElementById('xGrid').value);
        const yGrid = parseInt(document.getElementById('yGrid').value);

        const category = document.getElementById('dataCategory').value;
        const datatype = document.getElementById('dataType').value;

        const result = [];

        const xStep = (x2 - x1) / (xGrid - 1);
        const yStep = (y2 - y1) / (yGrid - 1);

        let options = ['A', 'B', 'C', 'D', 'E', 'F'];
        let tit = '';

        if (category === 'Question') {
            options = ['A', 'B', 'C', 'D', 'E', 'F'];
            for (let r = 0; r < yGrid; r++) {

                const opt = options[r] || String(r + 1);

                for (let c = 0; c < xGrid; c++) {

                    result.push({
                        label: `Q${c + 1}:${opt}`,
                        type: "Question",
                        x: +(x1 + c * xStep).toFixed(16),
                        y: +(y1 + r * yStep).toFixed(16),
                        optionValue: opt
                    });

                }
            }
        } else {
            if (datatype == 'Roll') {
                tit = 'Roll No';
                options = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
            } else if (datatype == 'Subject') {
                tit = 'Sub Code';
                options = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
            } else if (datatype == 'Setcode') {
                tit = 'Set Code';
                options = ["A", "B", "C", "D", "E"];
            }
            for (let r = 0; r < yGrid; r++) {

                const opt = options[r] || String(r + 1);

                for (let c = 0; c < xGrid; c++) {

                    result.push({

                        label: tit === 'Set Code' ? `${tit}:V${opt}` : `${tit}:D${c + 1}:V${opt}`,
                        type: "OMR_INFO",
                        x: +(x1 + c * xStep).toFixed(16),
                        y: +(y1 + r * yStep).toFixed(16),
                        optionValue: opt
                    });
                }
            }

        }

        document.getElementById('jsonOutput').value =
            JSON.stringify(result, null, 4);

        if (category == 'Question') {
            document.getElementById('jsonQuestion').value = JSON.stringify(result, null, 4);
        } else {
            alert(datatype);
            if (datatype == 'Roll') {
                document.getElementById('jsonRoll').value = JSON.stringify(result, null, 4);
            } else if (datatype == 'Subject') {
                document.getElementById('jsonSubject').value = JSON.stringify(result, null, 4);
            } else if (datatype == 'Setcode') {
                document.getElementById('jsonSet').value = JSON.stringify(result, null, 4);
            } else {
                document.getElementById('jsonMain').value = JSON.stringify(result, null, 4);
            }
        }





    };

</script>

<script>
    // ---------------- FINAL JSON GENERATOR ----------------
    document.getElementById('generateJsonFinal').onclick = () => {

        const omrName = document.getElementById("omrName").value;
        // image base64
        const imageBase64 = image.src || '';

        // safely parse json
        function parseJson(id) {

            const val = document.getElementById(id).value.trim();

            if (!val) return [];

            try {
                return JSON.parse(val);
            } catch (e) {
                alert(id + ' contains invalid JSON');
                return [];
            }
        }

        // merge all points
        const rollData = parseJson('jsonRoll');
        const subjectData = parseJson('jsonSubject');
        const setData = parseJson('jsonSet');
        const questionData = parseJson('jsonQuestion');

        const allPoints = [
            ...rollData,
            ...subjectData,
            ...setData,
            ...questionData
        ];

        // final object
        const finalJson = {
            name: omrName,
            image_base64: imageBase64,
            points: allPoints
        };

        // output to jsonMain
        document.getElementById('jsonMain').value =
            JSON.stringify(finalJson, null, 4);


        // ---------------- SAVE FILE ----------------

        fetch('omr/templete/save_omr_json.php', {

            method: 'POST',

            headers: {
                'Content-Type': 'application/json'
            },

            body: JSON.stringify({
                omrName: omrName,
                jsonData: finalJson
            })

        })
            .then(res => res.json())
            .then(data => {

                if (data.status === 'success') {
                    alert('JSON Saved Successfully');
                } else {
                    alert(data.message);
                }

            })
            .catch(err => {
                console.log(err);
                alert('Save Failed');
            });

    };
</script>


</body>

</html>








<div style="width:100%" hidden>

    {
    "name": "OMR",
    "image_base64":
    "9k=",
    "points": [
    {
    "label": "Q1:A",
    "type": "QUESTION",
    "x": 0.09951421618461609,
    "y": 0.24164971709251404,
    "optionValue": "A"
    },
    {
    "label": "Q1:B",
    "type": "QUESTION",
    "x": 0.14245936274528503,
    "y": 0.24164971709251404,
    "optionValue": "B"
    },
    {
    "label": "Q1:C",
    "type": "QUESTION",
    "x": 0.18824386596679688,
    "y": 0.24164971709251404,
    "optionValue": "C"
    },
    {
    "label": "Q1:D",
    "type": "QUESTION",
    "x": 0.2380857616662979,
    "y": 0.24164971709251404,
    "optionValue": "D"
    },
    {
    "label": "Q2:A",
    "type": "QUESTION",
    "x": 0.09951421618461609,
    "y": 0.29164971709251403,
    "optionValue": "A"
    },
    {
    "label": "Q2:B",
    "type": "QUESTION",
    "x": 0.14245936274528503,
    "y": 0.29164971709251403,
    "optionValue": "B"
    },
    {
    "label": "Q2:C",
    "type": "QUESTION",
    "x": 0.18824386596679688,
    "y": 0.29164971709251403,
    "optionValue": "C"
    },
    {
    "label": "Q2:D",
    "type": "QUESTION",
    "x": 0.2380857616662979,
    "y": 0.29164971709251403,
    "optionValue": "D"
    },
    {
    "label": "Q3:A",
    "type": "QUESTION",
    "x": 0.09951421618461609,
    "y": 0.341649717092514,
    "optionValue": "A"
    },
    {
    "label": "Q3:B",
    "type": "QUESTION",
    "x": 0.14245936274528503,
    "y": 0.341649717092514,
    "optionValue": "B"
    },
    {
    "label": "Q3:C",
    "type": "QUESTION",
    "x": 0.18824386596679688,
    "y": 0.341649717092514,
    "optionValue": "C"
    },
    {
    "label": "Q3:D",
    "type": "QUESTION",
    "x": 0.2380857616662979,
    "y": 0.341649717092514,
    "optionValue": "D"
    },
    {
    "label": "Q4:A",
    "type": "QUESTION",
    "x": 0.09951421618461609,
    "y": 0.39164971709251406,
    "optionValue": "A"
    },
    {
    "label": "Q4:B",
    "type": "QUESTION",
    "x": 0.14245936274528503,
    "y": 0.39164971709251406,
    "optionValue": "B"
    },
    {
    "label": "Q4:C",
    "type": "QUESTION",
    "x": 0.18824386596679688,
    "y": 0.39164971709251406,
    "optionValue": "C"
    },
    {
    "label": "Q4:D",
    "type": "QUESTION",
    "x": 0.2380857616662979,
    "y": 0.39164971709251406,
    "optionValue": "D"
    },
    {
    "label": "Q5:A",
    "type": "QUESTION",
    "x": 0.09951421618461609,
    "y": 0.44164971709251405,
    "optionValue": "A"
    },
    {
    "label": "Q5:B",
    "type": "QUESTION",
    "x": 0.14245936274528503,
    "y": 0.44164971709251405,
    "optionValue": "B"
    },
    {
    "label": "Q5:C",
    "type": "QUESTION",
    "x": 0.18824386596679688,
    "y": 0.44164971709251405,
    "optionValue": "C"
    },
    {
    "label": "Q5:D",
    "type": "QUESTION",
    "x": 0.2380857616662979,
    "y": 0.44164971709251405,
    "optionValue": "D"
    },
    {
    "label": "Q6:A",
    "type": "QUESTION",
    "x": 0.09951421618461609,
    "y": 0.49164971709251404,
    "optionValue": "A"
    },
    {
    "label": "Q6:B",
    "type": "QUESTION",
    "x": 0.14245936274528503,
    "y": 0.49164971709251404,
    "optionValue": "B"
    },
    {
    "label": "Q6:C",
    "type": "QUESTION",
    "x": 0.18824386596679688,
    "y": 0.49164971709251404,
    "optionValue": "C"
    },
    {
    "label": "Q6:D",
    "type": "QUESTION",
    "x": 0.2380857616662979,
    "y": 0.49164971709251404,
    "optionValue": "D"
    },
    {
    "label": "Q7:A",
    "type": "QUESTION",
    "x": 0.09951421618461609,
    "y": 0.5416497170925141,
    "optionValue": "A"
    },
    {
    "label": "Q7:B",
    "type": "QUESTION",
    "x": 0.14245936274528503,
    "y": 0.5416497170925141,
    "optionValue": "B"
    },
    {
    "label": "Q7:C",
    "type": "QUESTION",
    "x": 0.18824386596679688,
    "y": 0.5416497170925141,
    "optionValue": "C"
    },
    {
    "label": "Q7:D",
    "type": "QUESTION",
    "x": 0.2380857616662979,
    "y": 0.5416497170925141,
    "optionValue": "D"
    },
    {
    "label": "Q8:A",
    "type": "QUESTION",
    "x": 0.09951421618461609,
    "y": 0.5916497170925141,
    "optionValue": "A"
    },
    {
    "label": "Q8:B",
    "type": "QUESTION",
    "x": 0.14245936274528503,
    "y": 0.5916497170925141,
    "optionValue": "B"
    },
    {
    "label": "Q8:C",
    "type": "QUESTION",
    "x": 0.18824386596679688,
    "y": 0.5916497170925141,
    "optionValue": "C"
    },
    {
    "label": "Q8:D",
    "type": "QUESTION",
    "x": 0.2380857616662979,
    "y": 0.5916497170925141,
    "optionValue": "D"
    },
    {
    "label": "Q9:A",
    "type": "QUESTION",
    "x": 0.09951421618461609,
    "y": 0.641649717092514,
    "optionValue": "A"
    },
    {
    "label": "Q9:B",
    "type": "QUESTION",
    "x": 0.14245936274528503,
    "y": 0.641649717092514,
    "optionValue": "B"
    },
    {
    "label": "Q9:C",
    "type": "QUESTION",
    "x": 0.18824386596679688,
    "y": 0.641649717092514,
    "optionValue": "C"
    },
    {
    "label": "Q9:D",
    "type": "QUESTION",
    "x": 0.2380857616662979,
    "y": 0.641649717092514,
    "optionValue": "D"
    },
    {
    "label": "Q10:A",
    "type": "QUESTION",
    "x": 0.09951421618461609,
    "y": 0.6916497170925141,
    "optionValue": "A"
    },
    {
    "label": "Q10:B",
    "type": "QUESTION",
    "x": 0.14245936274528503,
    "y": 0.6916497170925141,
    "optionValue": "B"
    },
    {
    "label": "Q10:C",
    "type": "QUESTION",
    "x": 0.18824386596679688,
    "y": 0.6916497170925141,
    "optionValue": "C"
    },
    {
    "label": "Q10:D",
    "type": "QUESTION",
    "x": 0.2380857616662979,
    "y": 0.6916497170925141,
    "optionValue": "D"
    },
    {
    "label": "Q11:A",
    "type": "QUESTION",
    "x": 0.09951421618461609,
    "y": 0.7416497170925141,
    "optionValue": "A"
    },
    {
    "label": "Q11:B",
    "type": "QUESTION",
    "x": 0.14245936274528503,
    "y": 0.7416497170925141,
    "optionValue": "B"
    },
    {
    "label": "Q11:C",
    "type": "QUESTION",
    "x": 0.18824386596679688,
    "y": 0.7416497170925141,
    "optionValue": "C"
    },
    {
    "label": "Q11:D",
    "type": "QUESTION",
    "x": 0.2380857616662979,
    "y": 0.7416497170925141,
    "optionValue": "D"
    },
    {
    "label": "Q12:A",
    "type": "QUESTION",
    "x": 0.09951421618461609,
    "y": 0.7916497170925142,
    "optionValue": "A"
    },
    {
    "label": "Q12:B",
    "type": "QUESTION",
    "x": 0.14245936274528503,
    "y": 0.7916497170925142,
    "optionValue": "B"
    },
    {
    "label": "Q12:C",
    "type": "QUESTION",
    "x": 0.18824386596679688,
    "y": 0.7916497170925142,
    "optionValue": "C"
    },
    {
    "label": "Q12:D",
    "type": "QUESTION",
    "x": 0.2380857616662979,
    "y": 0.7916497170925142,
    "optionValue": "D"
    },
    {
    "label": "Q13:A",
    "type": "QUESTION",
    "x": 0.09951421618461609,
    "y": 0.8416497170925141,
    "optionValue": "A"
    },
    {
    "label": "Q13:B",
    "type": "QUESTION",
    "x": 0.14245936274528503,
    "y": 0.8416497170925141,
    "optionValue": "B"
    },
    {
    "label": "Q13:C",
    "type": "QUESTION",
    "x": 0.18824386596679688,
    "y": 0.8416497170925141,
    "optionValue": "C"
    },
    {
    "label": "Q13:D",
    "type": "QUESTION",
    "x": 0.2380857616662979,
    "y": 0.8416497170925141,
    "optionValue": "D"
    },
    {
    "label": "Q14:A",
    "type": "QUESTION",
    "x": 0.09951421618461609,
    "y": 0.8916497170925141,
    "optionValue": "A"
    },
    {
    "label": "Q14:B",
    "type": "QUESTION",
    "x": 0.14245936274528503,
    "y": 0.8916497170925141,
    "optionValue": "B"
    },
    {
    "label": "Q14:C",
    "type": "QUESTION",
    "x": 0.18824386596679688,
    "y": 0.8916497170925141,
    "optionValue": "C"
    },
    {
    "label": "Q14:D",
    "type": "QUESTION",
    "x": 0.2380857616662979,
    "y": 0.8916497170925141,
    "optionValue": "D"
    },
    {
    "label": "Q15:A",
    "type": "QUESTION",
    "x": 0.09951421618461609,
    "y": 0.9416497170925142,
    "optionValue": "A"
    },
    {
    "label": "Q15:B",
    "type": "QUESTION",
    "x": 0.14245936274528503,
    "y": 0.9416497170925142,
    "optionValue": "B"
    },
    {
    "label": "Q15:C",
    "type": "QUESTION",
    "x": 0.18824386596679688,
    "y": 0.9416497170925142,
    "optionValue": "C"
    },
    {
    "label": "Q15:D",
    "type": "QUESTION",
    "x": 0.2380857616662979,
    "y": 0.9416497170925142,
    "optionValue": "D"
    },
    {
    "label": "Roll No:D1:V0",
    "type": "OMR_INFO",
    "x": 0.524268627166748,
    "y": 0.2243836522102356,
    "optionValue": "0"
    },
    {
    "label": "Roll No:D2:V2",
    "type": "OMR_INFO",
    "x": 0.5578330755233765,
    "y": 0.2772468030452728,
    "optionValue": "2"
    },
    {
    "label": "Roll No:D3:V0",
    "type": "OMR_INFO",
    "x": 0.5975002646446228,
    "y": 0.22525741159915924,
    "optionValue": "0"
    },
    {
    "label": "Sub Code:D1:V1",
    "type": "OMR_INFO",
    "x": 0.7577497959136963,
    "y": 0.24839231371879578,
    "optionValue": "1"
    },
    {
    "label": "Sub Code:D3:V1",
    "type": "OMR_INFO",
    "x": 0.838613748550415,
    "y": 0.251743882894516,
    "optionValue": "1"
    },
    {
    "label": "Sub Code:D2:V0",
    "type": "OMR_INFO",
    "x": 0.7998841404914856,
    "y": 0.2266073226928711,
    "optionValue": "0"
    },
    {
    "label": "Set Code:VB",
    "type": "OMR_INFO",
    "x": 0.933937668800354,
    "y": 0.26052024960517883,
    "optionValue": "B"
    },
    {
    "label": "Roll No:D1:V9",
    "type": "OMR_INFO",
    "x": 0.5200045108795166,
    "y": 0.4479341506958008,
    "optionValue": "9"
    },
    {
    "label": "Roll No:D6:V9",
    "type": "OMR_INFO",
    "x": 0.7076615691184998,
    "y": 0.4468285143375397,
    "optionValue": "9"
    }
    ],
    "version": "1.0"
    }
</div>