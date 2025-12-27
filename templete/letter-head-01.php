<style>
    #padbox{
        display: inline-table;   /* সবচেয়ে গুরুত্বপূর্ণ */
        margin: 0 auto;
        border-collapse: collapse;
    }

    #padbox,
    #padbox tr,
    #padbox td{
        border: 0;
        padding: 0;
    }

    .a{
        font-size:20px;
        font-weight:700;
        line-height:24px;
    }

    .b{
        font-size:15px;
        font-weight:400;
        line-height:18px;
    }

    .c{
        font-size:12px;
        font-style:italic;
        line-height:16px;
    }

    .code{
        text-align:center;
        font-size:11px;
        font-weight:700;
    }
</style>

<div style="text-align:center;">
    <table id="padbox">
        <tr>
            <td style="padding-right:10px;">
                <img src="https://eimbox.com/logo/<?php echo $sccode; ?>.png" width="80">
            </td>
            <td>
                <div class="a"><?php echo $scname; ?></div>
                <div class="b"><?php echo $scaddress; ?></div>
                <div class="c">
                    <?php echo 'Mobile : '.$scmobile.' &nbsp;&nbsp; Email : '.$scmail; ?>
                </div>
                <div class="c"><?php echo 'Web : '.$scweb; ?></div>
            </td>
        </tr>
    </table>
</div>
