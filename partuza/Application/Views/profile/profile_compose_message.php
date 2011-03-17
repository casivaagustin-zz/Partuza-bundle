<div class="message_compose">
  <div class="form_entry">
    <div class="form_label"><label for="to">To</label></div>
    <select name="to" id="to" style="width:364px">
    	<option value="">Select one..</option>
<?php
  foreach ($friends as $friend) {
    echo "      <option value=\"{$friend['id']}\">{$friend['first_name']} {$friend['last_name']}</option>\n";
  }
?>
  	</select>
  </div>
  <div class="form_entry">
      <div class="form_label"><label for="subject">Subject</label></div>
      <input type="text" name="subject" id="subject" value="" style="width:364px" />
    </div>
  <div class="form_entry">
      <div class="form_label"><label for="subject">Message</label></div>
      <textarea name="message" id="message" style="height:220px; width:364px"></textarea>
  </div>
  <div class="form_entry" style="margin-top:12px">
    <div class="form_label"></div>
    <input type="button" id="compose_send" value="send" style="width:auto" class="button" />&nbsp;&nbsp;<input type="button" id="compose_cancel" value="cancel" style="width:auto" class="button" />
  </div>
</div>
