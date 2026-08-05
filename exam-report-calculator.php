<?php
require_once 'header.php';

$sy   = $_GET['sy']   ?? '';
$exam = $_GET['exam'] ?? '';
$slot = $_GET['slot'] ?? '';
?>

<div class="container-xxl flex-grow-1 container-p-y">

    <div class="row">

        <div class="col-md-12">

            <div class="card">

                <div class="card-header">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <h4 class="mb-1">
                                Exam Analytics Engine
                            </h4>

                            <small class="text-muted">

                                Session :
                                <strong><?= htmlspecialchars($sy) ?></strong>

                                |

                                Exam :
                                <strong><?= htmlspecialchars($exam) ?></strong>

                                |

                                Slot :
                                <strong><?= htmlspecialchars($slot) ?></strong>

                            </small>

                        </div>

                        <div>

                            <button
                                class="btn btn-primary"
                                id="btnStart">

                                <i class="ti ti-player-play"></i>

                                Start Calculation

                            </button>

                        </div>

                    </div>

                </div>

                <div class="card-body">

                    <!-- Progress -->

                    <div class="progress mb-4" style="height:25px">

                        <div
                            class="progress-bar progress-bar-striped progress-bar-animated"
                            id="progressBar"
                            style="width:0%">

                            0%

                        </div>

                    </div>

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover">

                            <thead class="table-light">

                            <tr>

                                <th width="70">#</th>

                                <th>Calculation Step</th>

                                <th width="180">Status</th>

                            </tr>

                            </thead>

                            <tbody id="stepBody">

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <br>

    <div class="card">

        <div class="card-header">

            <h5 class="mb-0">

                Live Processing Log

            </h5>

        </div>

        <div class="card-body">

            <div
                id="logArea"
                style="
                height:300px;
                overflow:auto;
                background:#000;
                color:#00ff00;
                padding:15px;
                font-family:Consolas;
                font-size:13px;">

            </div>

        </div>

    </div>

</div>

<?php require_once 'footer.php'; ?>

<script>

const examInfo = {

    sy   : "<?= htmlspecialchars($sy) ?>",
    exam : "<?= htmlspecialchars($exam) ?>",
    slot : "<?= htmlspecialchars($slot) ?>"

};

const steps = [

    {action:'load_exam', title:'Load Exam Information'},

    {action:'load_classes', title:'Load Classes'},

    {action:'load_sections', title:'Load Sections'},

    {action:'load_students', title:'Load Students'},

    {action:'load_subjects', title:'Load Subjects'},

    {action:'load_teachers', title:'Load Teachers'},

    {action:'build_cai', title:'Build Class Academic Index'},

    {action:'build_spi', title:'Build Subject Performance Index'},

    {action:'build_sgi', title:'Build Student Growth Index'},

    {action:'pass_rate', title:'Calculate Pass Rate'},

    {action:'excellent_rate', title:'Calculate Excellence Rate'},

    {action:'failure_rate', title:'Calculate Failure Rate'},

    {action:'difficulty_factor', title:'Calculate Class Difficulty Factor'},

    {action:'teacher_impact', title:'Calculate Teacher Impact'},

    {action:'adjusted_teacher_impact', title:'Calculate Adjusted Teacher Impact'},

    {action:'teacher_score', title:'Calculate Teacher Performance Score'},

    {action:'teacher_rank', title:'Generate Teacher Ranking'},

    {action:'subject_rank', title:'Generate Subject Ranking'},

    {action:'class_rank', title:'Generate Class Ranking'},

    {action:'ready', title:'Ready For Database Save'}

];

let tbody = document.getElementById("stepBody");

steps.forEach(function(step,index){

    tbody.innerHTML += `

    <tr>

        <td>${index+1}</td>

        <td>${step.title}</td>

        <td id="status${index}">

            <span class="badge bg-secondary">

                Pending

            </span>

        </td>

    </tr>

    `;

});

function addLog(text){

    let area=document.getElementById("logArea");

    area.innerHTML += text+"<br>";

    area.scrollTop=area.scrollHeight;

}

function updateProgress(i){

    let p=Math.round(((i+1)/steps.length)*100);

    document.getElementById("progressBar").style.width=p+"%";

    document.getElementById("progressBar").innerHTML=p+"%";

}

document.getElementById("btnStart").onclick=function(){

    this.disabled=true;

    addLog("====================================");

    addLog("Exam Analytics Started");

    addLog("====================================");

    processStep(0);

};

function processStep(index){

    if(index>=steps.length){

        addLog("");

        addLog("✔ ALL CALCULATIONS COMPLETED.");

        document.getElementById("btnStart").innerHTML="Completed";

        return;

    }

    document.getElementById("status"+index).innerHTML=

    '<span class="badge bg-warning">Processing...</span>';

    addLog("Processing : "+steps[index]);

    $.ajax({

        url:"backend/ajax/teacher-performance-calculation.php",

        type:"POST",

        data:{

            action:steps[index].action,

            sy:examInfo.sy,

            exam:examInfo.exam,

            slot:examInfo.slot

        },

        dataType:"json",

        success:function(res){

            if(res.status=="success"){

                document.getElementById("status"+index).innerHTML=

                '<span class="badge bg-success">✔ Completed</span>';

                addLog(res.message);

                updateProgress(index);

                processStep(index+1);

            }else{

                document.getElementById("status"+index).innerHTML=

                '<span class="badge bg-danger">Failed</span>';

                addLog("ERROR : "+res.message);

            }

        },

        error:function(){

            document.getElementById("status"+index).innerHTML=

            '<span class="badge bg-danger">Ajax Error</span>';

            addLog("AJAX ERROR.");

        }

    });

}

</script>

</body>
</html>