<?php 
  if(
    current_user_can( 'administrator' )
  ) :
?>

<?php endif; ?>

<select name="by_status[]" multiple multiselect-select-all="true" multiselect-max-items="3">
  <?php foreach( $status[ 'choices' ] as $key => $value ) : ?>
    <option value="<?= $key ?>" <?= (( isset( $inputs[ 'by_status' ] ) && in_array( $key, $inputs[ 'by_status' ] ) ) ? 'selected="selected"' : "") ?> ><?= $value ?></option>
  <?php endforeach; ?>
</select>