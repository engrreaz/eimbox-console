<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row gy-6">
        <!-- Video Player -->
        <div class="col-12">
            <div class="card">
                <h5 class="card-header">Video</h5>
                <div class="card-body"> 
                    <iframe src="https://www.youtube.com/embed/Cn4G2lZ_g2I"
                        style="height:50vh;"
                        title="YouTube video player" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        class="w-100 " poster="https://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-HD.jpg"
                        id="plyr-video-player" playsinline controls
                        allowfullscreen>
                    </iframe>
                </div>
            </div>
        </div>
        <!-- /Video Player -->

    </div>
</div>

<?php require_once 'footer.php'; ?>

<!-- ----------------------------------- -->
<script></script>
<!-- ----------------------------------- -->
</body>

</html>