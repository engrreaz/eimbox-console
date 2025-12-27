<style>
    .header-wrap{
        width:100%;
        display:flex;
        justify-content:center;   /* পুরো ব্লক মাঝখানে */
    }

    .header-box{
        display:flex;
        align-items:center;
        gap:10px;                 /* logo + text gap */
        text-align:left;
    }

    .a{
        font-size:20px;
        font-weight:700;
        line-height:24px;
    }

    .b{
        font-size:15px;
        line-height:18px;
    }

    .c{
        font-size:12px;
        font-style:italic;
        line-height:16px;
    }
</style>

<div class="header-wrap">
    <div class="header-box">
        <img src="https://eimbox.com/logo/<?php echo $sccode; ?>.png" width="80">

        <div>
            <div class="a"><?php echo $scname; ?></div>
            <div class="b"><?php echo $scaddress; ?></div>
            <div class="c">
                <?php echo 'Mobile : '.$scmobile.' | Email : '.$scmail; ?>
            </div>
            <div class="c"><?php echo 'Web : '.$scweb; ?></div>
        </div>
    </div>
</div>
