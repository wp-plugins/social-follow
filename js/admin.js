function sf_checkButtonCode() {
	var result = jQuery('#sf_button_code_check_result');
	result.show().addClass('sf_button_code_check_result_wait').html(jQuery("#jsChecking").html());
	success = /<img src="http:\/\/www.socialfollow.com\/button\/image\/\?b=([\d]+)(?:&image=[^"]+)?" class="socialFollowImage" alt="[^"]+" \/>\s+<script type="text\/javascript" src="http:\/\/www.socialfollow.com\/button\/\?b=\1"><\/script>\s+<script type="text\/javascript">socialfollow.init\("socialFollowImage"\);<\/script>/.test(jQuery('#sf_button_code').val());
	response = (success == true) ? jQuery("#jsSuccess").html() : jQuery("#jsFailure").html();
	result.html(response).removeClass('sf_button_code_check_result_wait');
	setTimeout('sf_checkButtonCodeResult();', 5000);
};

function sf_checkButtonCodeResult() {
	jQuery('#sf_button_code_check_result').fadeOut('slow');
};