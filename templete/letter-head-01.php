<style>
    .lh-table {
        margin: 0 auto;
        /* পুরো ব্লক মাঝখানে */
        border: 0;
    }

    .lh-table td {
        border: 0;
        vertical-align: middle;
    }

    .a {
        font-size: 20px;
        font-weight: 700;
        line-height: 24px;
    }

    .b {
        font-size: 15px;
        line-height: 18px;
    }

    .c {
        font-size: 12px;
        font-style: italic;
        line-height: 16px;
    }
</style>

<div>
    <table class="lh-table">
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
</div>