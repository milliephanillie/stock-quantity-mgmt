<div class="wrap" id="pscaff-admin">

<div class="pscaff--page-title-wrapper">
    <div class="bf--page-header">
    <?php
        echo '<h1><span class="dashicons dashicons-cart"></span> Manage Stock</h1>';
        settings_errors();
    ?>
    </div>
</div>

	<div class="pscaff_content_wrapper">
		<form method="post" action="options.php">
			<?php
					settings_fields('stock-quantities-table-options');
					do_settings_sections('sqmgmt-settings');
					submit_button();
			?>
		</form>
	</div>
</div>
