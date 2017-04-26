<?php
ob_start();
include $_SERVER['DOCUMENT_ROOT'] . '/templates/corp_template_start.html';
$htmlstart = ob_get_contents();
ob_end_clean();

ob_start();
include $_SERVER['DOCUMENT_ROOT'] . '/templates/corp_template_end.html';
$htmlend = ob_get_contents();
ob_end_clean();




$html = <<<EOT
{$htmlstart}
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tbody>
		<tr bgcolor="#FFFFFF">
			<td class="fullpad" style="padding: 20px 30px 1px;">
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tbody>
					<tr>
						<td align="left" valign="top" style="font-size: 18px; color: #ee7421; line-height: 24px; padding: 0px 0px 20px;">
						<font style="font-family: 'Arial Narrow', Helvetica, sans-serif;"> 
						<!--[if (!mso 14)&(!mso 15)]><!--> 
						<font style="font-family: Oswald, 'Arial Narrow', Helvetica, Arial, sans-serif;"> 
						<!--<![endif]--> 
						NEW MEMBER
						<!--[if (!mso 14)&(!mso 15)]><!--> 
						</font> 
						<!--<![endif]--></font>
						</td>
					</tr>
				</tbody>
			</table>
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tbody>
					<tr>
						<td class="blockme" style="padding-right: 20px;">
						<table width="100%" border="0" cellspacing="0" cellpadding="0">
							<tbody>
								<tr>
									<td align="left" valign="top" style="font-size: 25px; color: #55565a; line-height: 34px;">
									<font style="font-family: 'Arial Narrow', Helvetica, sans-serif;"> 
									<!--[if (!mso 14)&(!mso 15)]><!--> 
									<font style="font-family: Oswald, 'Arial Narrow', Helvetica, Arial, sans-serif;"> 
									<!--<![endif]--> 
									$header
									<!--[if (!mso 14)&(!mso 15)]><!--> 
									</font> 
									<!--<![endif]--></font>
									</td>
								</tr>
								<tr>
									<td>
									<p style="font-family: Helvetica, Arial, sans-serif; font-size: 13px; line-height: 16px; color: #434448; padding-top: 5px;">
					
									$description
						
									</p>
									</td>
								</tr>
							</tbody>
						</table>
						</td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
	</tbody>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tbody>
		<tr>
			<td class="fullpad" style="padding: 0px 30px 5px;">
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tbody>
					<tr>
						<td style="padding-top: 15px;">
						<table class="headlinebar" width="100%" border="0" cellspacing="0" cellpadding="0">
							<tbody>
								<tr>
									<td class="subheadline" align="left" valign="top" style="font-size: 18px; color: #ee7421; border-top-width: 1px; border-top-style: solid; border-top-color: #dfe0e0; padding-top: 15px;"><font style="font-family: 'Arial Narrow', Helvetica, sans-serif;"> 
									<!--[if (!mso 14)&(!mso 15)]><!--> 
									<font style="font-family: Oswald, 'Arial Narrow', Helvetica, Arial, sans-serif;"> 
									<!--<![endif]--> 
								LET'S WELCOME THEM ON SOCIAL MEDIA
									<!--[if (!mso 14)&(!mso 15)]><!--> 
									</font> 
									<!--<![endif]--></font></td>
								</tr>
							</tbody>
						</table>
						</td>
					</tr>
					<tr>
						<td style="padding-top: 30px;">
						<table width="100%" border="0" cellspacing="0" cellpadding="0">
							<tbody>
								<tr>
									<td class="blockme" style="padding-right: 5px;" width="50%" valign="top" align="left">
									<table width="100%" border="0" cellspacing="0" cellpadding="0">
										<tbody>
											<tr>
												<!-------TWITTER BEGIN------->
												<td width="50%" style="padding-right: 5px;" valign="top">
												<table width="100%" border="0" cellspacing="0" cellpadding="0">
													<tbody>
														<tr>
															<td class="imagecont" style="padding-bottom: 10px;">
															<!----------TWITTER LINK----------><a href="$twitter_link">
															<img class="fullwidth" src="http://image.updates.sandiego.org/lib/fe9e15707566017871/m/5/icon-social-twitter-reverse.jpg" border="0" style="display: block; height: auto !important;" width="127" />
															</a></td>
														</tr>
													</tbody>
												</table>
												</td>
												<!-------TWITTER END------->
												<!-------FACEBOOK BEGIN------->
												<td width="50%" style="padding-left: 5px;" valign="top">
												<table width="100%" border="0" cellspacing="0" cellpadding="0">
													<tbody>
														<tr>
															<td class="imagecont" style="padding-bottom: 10px;">
															<!----------FACEBOOK LINK----------><a href="$facebook_link">
															<img class="fullwidth" src="http://image.updates.sandiego.org/lib/fe9e15707566017871/m/5/icon-social-facebook-reverse.jpg" border="0" style="display: block; height: auto !important;" width="127" />
															</a></td>
														</tr>
													</tbody>
												</table>
												</td>
											</tr>
										</tbody>
									</table>
									</td>
									<!-------FACEBOOK END------->
									<!-------GOOGLE PLUS BEGIN------->
									<td class="blockme" style="padding-left: 5px;" width="50%" valign="top" align="left">
									<table width="100%" border="0" cellspacing="0" cellpadding="0">
										<tbody>
											<tr>
												<td width="50%" style="padding-right: 5px;" valign="top">
												<table width="100%" border="0" cellspacing="0" cellpadding="0">
													<tbody>
														<tr>
															<td class="imagecont" style="padding-bottom: 10px;">
															<!----------GOOGLE PLUS LINK----------><a href="$googleplus_link">
															<img class="fullwidth" src="http://image.updates.sandiego.org/lib/fe9e15707566017871/m/5/icon-social-googleplus-reverse.jpg" border="0" style="display: block; height: auto !important;" width="127" />
															</a></td>
														</tr>
													</tbody>
												</table>
												</td>
												<!-------GOOGLE PLUS END------->
												<!-------LINKED IN BEGIN------->
												<td width="50%" style="padding-left: 5px;" valign="top">
												<table width="100%" border="0" cellspacing="0" cellpadding="0">
													<tbody>
														<tr>
															<td class="imagecont" style="padding-bottom: 10px;">
															<!----------LINKED IN LINK----------><a href="$linkedin_link">
															<img class="fullwidth" src="http://image.updates.sandiego.org/lib/fe9e15707566017871/m/5/icon-social-linkedin-reverse.jpg" border="0" style="display: block; height: auto !important;" width="127" />
															</a></td>
														</tr>
													</tbody>
												</table>
												</td>
												<!-------LINKED IN END------->
											</tr>
										</tbody>
									</table>
									</td>
								</tr>
							</tbody>
						</table>
						</td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
	</tbody>
</table>
EOT;

//if there is an image url, build this section, if not leave it out            
if(!empty($_POST['Iurl'])){
$html .= <<<EOT
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tbody>
		<tr>
			<td class="fullpad" bgcolor="#ffffff" style="padding: 20px 30px 30px;">
			<table border="0" width="100%" cellspacing="0" cellpadding="0">
				<tbody>
					<tr>
						<td style="padding-top: 29px;">
						<p><a href="$sharelink" style="font-family: Helvetica, Arial, sans-serif; font-size: 13px; line-height: 16px; color: #434448; padding-top: 5px;">SEE WHAT YOU ARE SHARING</a></p>
						
						<img class="fullwidth" src="$Iurl" width="540" border="0" style="display: block; height: auto !important;" /></td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
	</tbody>
</table>
EOT;
}



$html .= <<<EOT
<!-- CONTACTS 1 -->
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tbody>
		<tr>
			<td style="padding: 0px 30px 18px;">
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tbody>
					<tr>
						<td style="padding-right: 5px;" width="50%" valign="top" align="left">
						<table width="100%" border="0" cellspacing="0" cellpadding="0">
							<tbody>
								<tr>
									<td class="blockme">
									<br />
									&nbsp;<br />
									<p style="font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #434448; padding: 0px; margin: 0px;">
									<strong>$user->name</strong><br />
									$user->title<br />
									<a href="mailto:$user->email?subject=Question about Social Sharing">$user->email</a><br />
									$user->phone
									<br />
									<br />
									<a style="text-decoration: none;" href="$user->twitterlink">$user->twitterhash</a> on Twitter
									</p>
									</td>
								</tr>
							</tbody>
						</table>
						</td>
						<td style="padding-right: 15px;">
						</td>
					</tr>
				</tbody>
			</table>
			</td>
		</tr>
	</tbody>
</table>
$htmlend;
EOT;

?>