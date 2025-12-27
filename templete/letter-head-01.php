<style>
    #letter-head .td {
        border: 0;
    }

    .a {
        font-size: 20px;
        font-weight: 700;
        line-height: 24px;
        display: block;
    }

    .b {
        font-size: 15px;
        line-height: 18px;
        display: block;
    }

    .c {
        font-size: 12px;
        font-style: italic;
        line-height: 16px;
        display: block;
    }
</style>



<table id="letter-head" style="margin:auto; border:0;">
    <tr>
        <td style="padding-right:10px; width:90px;">
            <img src="https://eimbox.com/logo/<?php echo $sccode; ?>.png" width="80">
        </td>
        <td>
            <div class="a"><?php echo $scname; ?></div>
            <div class="b"><?php echo $scaddress; ?></div>
            <div class="c">
                <?php echo 'Mobile : ' . $scmobile . ' | Email : ' . $scmail; ?>
            </div>
            <div class="c"><?php echo 'Web : ' . $scweb; ?></div>
        </td>
    </tr>
</table>