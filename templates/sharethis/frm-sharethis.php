<?php
$html = <<<EOT
<form class="form" title="tttdform" name="tttdform" action="index.php?tmp=sharethis" method="post">
<div class="row">
<div class="first-col cols_12-6 column">
	$email_name
</div>
<div class="cols_12-6 column">
$email_subject
</div>
<div class="cols_12-4"></div>
</div>

<div class="row">
	<div class="first-col cols_12-4 column">	$header  </div>
  <div class="cols_12-4 column">  $share_link  </div>
  <div class="cols_12-4 column">
$username
	</div>
</div>

<div class="row">
  <div class="first-col cols_12-8 column">
$description

		</div>
  </div>
</div>
<div class="row">
  <div class="first-col cols_12-12 column">
$twitter_warning
  $twitter_text
    <div><span id="charcount">120</span> Characters Remaining</div>
  </div>
</div>
<div class="row">
  <div class="first-col cols_12-4 column">
    $linkedin_title
  </div>
  <div class="cols_12-4 column">
    $linkedin_src
  </div>
  <div class="cols_12-4 column">
	$linkedin_sum
  </div>
</div>

<div class="row">
  <div class="first-col cols_12-6 column">
	$image_url
  <label for="medium">Image URL (must be precropped to 540 pixels wide)</label>
  <input class="input text drop-area" name="Iurl" type="text" size="25" data-width="540" data-height="215">
  </div>
</div>

<div class="row">
<input style="clear:both;float:none;" type="submit" value="Generate HTML" class="submit">
</div>


</form>
EOT;
