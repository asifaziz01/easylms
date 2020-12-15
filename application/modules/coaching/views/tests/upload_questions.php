<!-- IMPORT QUESTIONS -->
<div class="row justify-content-center">
	<div class="col-md-8 ">
        <div class="list-group">
			<a class="list-group-item" data-toggle="modal" data-target="#import_text"><i class="fa fa-upload"></i> Import From TEXT File</a>
			<a class="list-group-item" data-toggle="modal" data-target="#import_csv"><i class="fa fa-upload"></i> Import From CSV File</a>
			<a class="list-group-item" data-toggle="modal" data-target="#import_word"><i class="fa fa-upload"></i> Import From Word File</a>
        </div>
    </div>
</div>

<!-- Modal for text form -->
<div class="modal fade" id="import_text" tabindex="-1" role="dialog" aria-labelledby="import_text" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
        <?php echo form_open_multipart ('coaching/qb_actions/import_from_text/'.$coaching_id.'/'.$course_id.'/'.$test_id, array ('class'=>'form-horizontal', 'id'=>'upload-from-text') ); ?>
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Select Text File</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
				<input type="file" name="userfile" size="20" class="form-control" />
				<p class="help-text">.txt files only</p>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
          </div>
        </form> 
    </div>
  </div>
</div>

<!-- Modal for CSV form -->
<div class="modal fade" id="import_csv" tabindex="-1" role="dialog" aria-labelledby="import_csv" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
        <?php echo form_open_multipart ('coaching/qb_actions/import_from_csv/'.$coaching_id.'/'.$course_id.'/'.$test_id, array ('class'=>'form-horizontal', 'id'=>'upload-from-csv') ); ?>
          <div class="modal-header">
            <h5 class="modal-title" id="">Select CSV File</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
				<input type="file" name="userfile" size="20" class="form-control" />
				<p class="help-text">.csv files only</p>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
          </div>
        </form> 
    </div>
  </div>
</div>

<!-- Modal for word form -->
<div class="modal fade" id="import_word" tabindex="-1" role="dialog" aria-labelledby="import_word" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
        <?php echo form_open_multipart ('coaching/qb_actions/import_from_word/'.$coaching_id.'/'.$course_id.'/'.$test_id, array ('class'=>'form-horizontal', 'class'=>'validate-form') ); ?>
          <div class="modal-header">
            <h5 class="modal-title" id="">Select Word File</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
				<input type="file" name="userfile" size="20" class="form-control" />
				<p class="help-text">.docx files only</p>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
          </div>
        </form> 
    </div>
  </div>
</div>