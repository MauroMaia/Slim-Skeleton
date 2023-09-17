"use strict"

$(document).ready(function () {
	$("#terms, #password, #password-conf").change(function () {

		const terms = $("#terms").select
		const password = $("#password").val()
		const password_conf = $("#password-conf").val()

		$("#register").prop("disabled",!(
			password.length >= 8
			&& password_conf.length >= 8
			&& password_conf === password
			&& terms
		))
	})
})
