<style>
    #padbox{
        margin:auto;
    }
    #padbox table, #padbox tr, #padbox td{
        border:0;
    }

    .a {
        font-size: 20px;
        font-weight: 700;
        font-style: normal;
        line-height: 24px;
    }

    .b {
        font-size: 15px;
        font-weight: 400;
        font-style: normal;
        line-height: 18px;
      
    }

    .c {
        font-size: 12px;
        font-weight: 400;
        font-style: italic;
        line-height: 16px;
    }


    .code {text-align:center; font-size:11px; font-weight:700;}
</style>

<table id="padbox" style="border:0; margin-bottom:20px;">
    <tr>
        <td style="text-align:center; padding-right:10px;">
            <img src="https://eimbox.com/logo/<?php echo $sccode; ?>.png" width="80" />
        </td>
        <td style="text-align:center;">
            <div class="a"><?php echo $scname; ?></div>
            <div class="b"><?php echo $scaddress; ?></div>
            <div class="c"><br><?php echo 'Mobile : ' . $mobile . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Email : ' . $scmail; ?></div>
            <div class="c"><?php echo 'Web : ' . $scweb; ?></div>
        </td>
    </tr>
</table>