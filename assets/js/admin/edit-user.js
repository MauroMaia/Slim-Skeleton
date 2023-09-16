"use strict"

$(document).ready(function () {
	$("#firstName, #lastName, #email, #jobTitle").change(function () {

		const firstName = $("#firstName").val()
		const lastName = $("#lastName").val()
		const email = $("#email").val()

		$("#save-basic").prop("disabled",!(
			firstName.length > 0 &&
			lastName.length > 0 &&
			email.length > 0
		))
	})

	$("#password, #password-conf").change(function () {

		const password = $("#password").val()
		const password_conf = $("#password-conf").val()

		$("#save-password").prop("disabled",!(
			password.length >= 8 &&
			password_conf.length >= 8 &&
			password_conf === password
		))
	})
})
