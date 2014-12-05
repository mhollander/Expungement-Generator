<?php
/* @todo add password reset 
/* @todo add register link
/****************************************************************
*
*	register.php
*	allows people to register for AutoExpunge
*
*	Copyright 2011-2015 Community Legal Services
* 
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
*
*    http://www.apache.org/licenses/LICENSE-2.0

* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*
****************************************************************/


	require_once("config.php");
	require_once("utils.php");
	include("head.php");

	// the page header, including the menu bar, etc...
	include("header.php");
?>
<!--
<div class="main">
	<div class="content-left">&nbsp;</div>
	<div class="content-center">
		<form action="login.php>" method="post">
		<div class="form-item">
			<label for="registerEmail">Email Address</label>
			<input type="text" name="registerEmail" id="registerEmail" class="form-text" /> 
			<div class="description">Your email address will be used as your login</div>
		</div> 
		<div class="form-item">
			<label for="registerPassword">Password</label>
			<input type="registerPassword" name="registerPassword" id="registerPassword" class="form-text" />
		</div>
		<div class="form-item">
			<label for="retypePassword">Retype Password</label>
			<input type="retypePassword" name="retypePassword" id="retypePassword" class="form-text" />
		</div>
				<div class="form-item">
			<label for="registerFirst">Your Name</label>
			<div class="form-item-column">
				<input type="text" name="registerFirst" id="registerFirst" class="form-text" value="<?php printIfSet('personFirst');?>" /> 
			</div>
			<div class="form-item-column">
				<input type="text" name="registerLast" id="registerLast" class="form-text" value="<?php printIfSet('personLast');?>" />
			</div>
			<div class="space-line"></div>
			<div class="description">
				<div class="form-item-column">
					First Name
				</div>
				<div class="form-item-column">
					Last Name
				</div>
			</div>
			<div class="space-line"></div>
		</div> 

		<div class="form-item">
			<input type="submit" value="Login" />
		</div>
		</form>
	</div>
	<div class="content-right">&nbsp;</div>
</div>
-->
<?php 
	include("foot.php");
?>