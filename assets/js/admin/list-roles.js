
fetch( "api/roles/list", {
	method: 'GET',
	credentials: 'same-origin', // Include cookies when making the request
} )
	.then(response => response.json())
	.then( function ( response )
	{
		console.log("response ", response)

		let fields =[ ]

		for (const responseKey of response.permissions) {
			fields = [{
				name:responseKey,
				type: "checkbox"

			}].concat(fields)
		}

		let roles =[ ]

		for (const role of response.roles) {
			for (const perm of role.permissions) {
				role[perm] = true
			}
			roles.push(role)
		}

		$("#jsGrid").jsGrid({
			width: "100%",
			height: "60%",

			inserting: true,
			editing: true,
			sorting: true,
			paging: false,

			data: roles,

			fields: [
				{
					name:"name",
					type: "text"

				}
			].concat(fields).concat([{ type: "control" }]),

		});
	} )


