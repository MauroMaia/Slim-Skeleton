"use strict"

$(document).ready(function () {

	$("[id^=\"delete-\"]").click(function () {
		const id = this.value
		if (confirm(`Delete User id #${id}?`)) {
			$.ajax({
				url: `secure/api/admin/user/${id}`,
				type: "DELETE",
				success: function() {
					location.reload()
				},
				error: function() {
					location.reload()
				}
			})
		}else {
			// No - notting
		}
	})

})
