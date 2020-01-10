<style>
    .microweber-server-box {
        width:960px;
        margin:0 auto;
    }
</style>
<div class="microweber-server-box">
<h1 style="font-weight: bold; margin-bottom: 10px;">License key mapping</h1>
<p>Map the license keys to the hosting plans.</p>

    <form method="post" action="addonmodules.php?module=microweber_server&function=save_mapping">
        <div id="tableBackground" class="tablebg">
            <table class="datatable no-margin" width="100%" cellspacing="1" cellpadding="3" border="0">

                <thead>
                <tr>
                    <th>License plan</th>
                    <th>Select hosting plan</th>
                </tr>
                </thead>

                <?php foreach($license_key_plans as $license_plan): ?>
                <tr>
                    <td><?php echo $license_plan->name; ?></td>
                    <td>
                        <select class="form-control" name="license_plan[<?php echo $license_plan->id; ?>]">
                            <option value="0">None</option>
                            <?php foreach($hosting_plans as $plan): ?>

                                <?php
                                $selected = '';
                                foreach ($mapping as $map) {
                                    if ($map->license_plan_id == $license_plan->id && $map->product_plan_id == $plan->id) {
                                        $selected = 'selected="selected"';
                                    }
                                }
                                ?>

                            <option value="<?php echo $plan->id; ?>" <?php echo $selected; ?>><?php echo $plan->name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <?php endforeach; ?>

            </table>
        </div>

        <button type="submit" class="btn btn-success">Save mapping</button>
    </form>

</div>