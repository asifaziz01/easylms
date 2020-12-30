<div class="card">
  <div class="card-body">
    <?php echo form_open_multipart ('coaching/tests_actions/upload_questions/'.$coaching_id.'/'.$course_id.'/'.$test_id, array ('class'=>'form-horizontal', 'id'=>'') ); ?>
        <h4 class="card-title" >Select File</h4>
        <div class="form-group">
			    <input type="file" name="userfile" size="20" class="form-control" />
			      <p class="help-text">.txt files only</p>
        </div>
        <div class="card-footer">
          <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
    </form> 
  </div>
</div>

