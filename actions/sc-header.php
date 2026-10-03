<table class="mt-4 mb-6 ">
    <tr>
        <td><img src="<?php echo institute_logo($sccode); ?> "
                onerror="this.onerror=null; this.src='https://eimbox.com/logo/<?php echo $sccode; ?>.png';"
                style="max-width:50px; max-height:50px;" />
        </td>
        <td style="width:15px; border-right:5px solid gray;"></td>
        <td style="width:15px;"></td>
        <td>
            <h3 class="m-0 p-0 fw-bold"><?= $scname; ?></h3>
            <h6 class="m-0 p-0"><?= $address; ?></h6>

        </td>
    </tr>
</table>