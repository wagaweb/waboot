<h2><?php _ex('Components', 'Updates list', 'waboot'); ?></h2>
<?php if($no_components): ?>
    <?php _ex('You don\'t have any theme component yet.', 'Updates list', 'waboot'); ?>
<?php elseif($all_updated): ?>
	<?php _ex('Your components are all up to date.', 'Updates list', 'waboot'); ?>
<?php else: ?>
    <table class="widefat updates-table" id="update-components-table">
        <thead>
        <!--<tr>
            <td class="manage-column check-column"><input type="checkbox" id="themes-select-all"></td>
            <td class="manage-column"><label for="themes-select-all">Select All</label></td>
        </tr>-->
        </thead>

        <tbody class="plugins">
        <?php foreach ($components_to_update as $component): ?>
        <tr>
            <!-- <td class="check-column">
                <input type="checkbox" name="checked[]" id="checkbox_48e2bbcb66ec6bb74044415aa3d5be14" value="twentyseventeen">
                <label for="checkbox_48e2bbcb66ec6bb74044415aa3d5be14" class="screen-reader-text">Select Twenty Seventeen</label>
            </td> -->
            <td class="plugin-title">
                <p>
                    <img src="<?php echo $component['thumbnail'] ?>" width="85" height="64" class="updates-table-screenshot" alt="">
                    <strong><?php echo $component['name'] ?></strong>
	                <?php _ex(
	                        sprintf(
	                                'You have version %s installed. <a href="%s">Update to %s</a>.',
                                    $component['current_version'],
                                    add_query_arg(['component'=>$component['slug'],'nicename'=>$component['name']],$update_form_action),
                                    $component['version']
                            ),
                            'Updates list',
                            'waboot'
                    );
	                ?>
                </p>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>

        <tfoot>
        <!-- <tr>
            <td class="manage-column check-column"><input type="checkbox" id="themes-select-all-2"></td>
            <td class="manage-column"><label for="themes-select-all-2">Select All</label></td>
        </tr> -->
        </tfoot>
    </table>
<?php endif; ?>
