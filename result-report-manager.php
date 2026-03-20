<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">

    <?php
    $chain_param = '-c 12 -t Gradebook Report -u -r -b View Result -h exam';
    include 'components/slot-tree-ui.php';
    ?>



    <div class="card card-custom">
        <div class="card-body">
            <div class="row g-3">

                <div class="col-6 col-md-3">
                    <button type="button" class="btn btn-primary w-100 p-3 btn-sm" onclick="tsheet();">
                        <i class="bi bi-table pe-5"></i> Tabulating Sheet
                    </button>
                </div>

                <div class="col-12 col-md-2">
                    <button type="button" class="btn btn-success w-100 p-3 btn-sm" onclick="preport();">
                        <i class="bi bi-bar-chart-line-fill pe-5"></i> Progress
                    </button>
                </div>

                <div class="col-6 col-md-2">
                    <button type="button" class="btn btn-warning w-100 p-3 btn-sm" onclick="stats();">
                        <i class="bi bi-pie-chart-fill pe-5"></i> Stats
                    </button>
                </div>

                <div class="col-6 col-md-3">
                    <button type="button" class="btn btn-info w-100 p-3 btn-sm" onclick="report();">
                        <i class="bi bi-file-earmark-text-fill pe-5"></i> Overview
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<!-- ----------------------------------- -->
<script></script>
<!-- ----------------------------------- -->




<script>
document.addEventListener('DOMContentLoaded', function () {

    function getVal(id){
        const el = document.getElementById(id);
        return el ? encodeURIComponent(el.value) : '';
    }

    function openPage(url, newTab=true){
        if(newTab){
            window.open(url, '_blank');
        }else{
            window.location.href = url;
        }
    }

    function buildQuery(params){
        return Object.keys(params)
            .map(k => k + '=' + params[k])
            .join('&');
    }

    function commonParams(){
        return {
            cls:  getVal('class-main'),
            sec:  getVal('section-main'),
            session: getVal('session-main'),
            exam: getVal('exam-main'),
            slot: getVal('slot-main'),
            v: ''
        };
    }

    // ===== Functions =====

    window.go = function(){
        const p = commonParams();
        const view = '<?php echo $view ?? ""; ?>';
        p.v = encodeURIComponent(view);
        p.year = p.session;

        const url = 'result-repo-select.php?' + buildQuery(p);
        openPage(url, false);
    };

    window.tsheet = function(){
        const p = commonParams();
        const url = 'tabulating-report.php?' + buildQuery({
            class: p.cls,
            section: p.sec,
            session: p.session,
            exam: p.exam,
            slot: p.slot,
            v: p.v
        });
        openPage(url);
    };

    window.preport = function(){
        const p = commonParams();
        p.sy = p.session;

        const url = 'progress-report.php?' + buildQuery({
            cls: p.cls,
            sec: p.sec,
            sy: p.sy,
            exam: p.exam,
            slot: p.slot,
            v: p.v
        });
        openPage(url);
    };

    window.stats = function(){
        const p = commonParams();
        p.sy = p.session;

        const url = 'tabulating-sheet.php?' + buildQuery({
            classname: p.cls,
            sectionname: p.sec,
            sy: p.sy,
            exam: p.exam,
            slot: p.slot,
            v: p.v,
            top: 'true'
        });
        openPage(url);
    };

    window.report = function(){
        const p = commonParams();
        p.sy = p.session;

        const url = 'tabulating-report.php?' + buildQuery({
            classname: p.cls,
            sectionname: p.sec,
            sy: p.sy,
            exam: p.exam,
            slot: p.slot,
            v: p.v,
            top: 'true'
        });
        openPage(url);
    };

});
</script>



</body>

</html>