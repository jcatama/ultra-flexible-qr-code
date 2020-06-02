<div class="wrap">
  <h2>Flexible QR List</h2>
  <br>
  <?php if(current_user_can('edit_posts')): ?>
  <div id="ufqc_add_new_qr">
    <form method="post" action="<?= esc_url(admin_url('admin-post.php')); ?>" >
      <input type="hidden" name="action" value="ufqc_submit_action">
      <label for="ufqc_label"><strong>LABEL</strong></label>
      <input type="text" id="ufqc_label" name="ufqc_label" required>
      <label for="ufqc_content"><strong>CONTENT</strong></label>
      <input type="text" id="ufqc_content" style="width: 320px;" name="ufqc_content" required>
      <button type="submit" class="button">Add</button>
    </form>
  </div>
  <?php endif; ?>
  <div id="poststuff">
    <div id="post-body" class="metabox-holder columns-2">
      <div id="post-body-content">
        <div class="meta-box-sortables ui-sortable">
          <form method="post">
          <input type="hidden" name="page" value="<?= esc_attr($_REQUEST['page']); ?>" />
          <?php
            $this->ufqc_qrs_obj->prepare_items();
            $this->ufqc_qrs_obj->search_box('Search', 'label');
            $this->ufqc_qrs_obj->display();
          ?>
          </form>
        </div>
      </div>
    </div>
    <br class="clear">
  </div>
</div>