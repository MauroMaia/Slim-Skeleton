
function loadData() {
    return fetch("api/roles/list", {
        method: "GET", credentials: "same-origin", // Include cookies when making the request
    }).then(response => response.json())
        .then(function (response) {
            let fields = []

            for (const responseKey of response.permissions) {
                fields = [{
                    name: responseKey, type: "checkbox"

                }].concat(fields)
            }

            let roles = []

            for (const role of response.roles) {
                for (const perm of role.permissions) {
                    role[perm] = true
                }
                roles.push(role)
            }

            return [fields,roles]
        })
}

$(document).ready(function ()
{
    loadData()
        .then(function ([fields,roles]) {

            $("#jsGrid").jsGrid({
                width: "100%", height: "100%",

                inserting: true,
                editing: true,
                sorting: true,
                paging: false,
                autoload: true,

                data: roles,

                fields: [
                    {
                        name: "id",
                        title: "#",
                        align: "center",
                        type: "number",
                        width: 20,
                        editing: false
                    },
                    {
                        name: "name",
                        type: "text",
                        align: "center",
                    }
                ].concat(fields).concat([{type: "control"}]),

                controller: {
                    insertItem: function (item) {
                        console.log("insertItem:", item)

                        return $.ajax({
                            type: "PUT", url: "api/roles/", data: item
                        }).then(() => location.reload());
                    },

                    updateItem: function (item) {
                        if (item == null || item.id <= 3) {
                            $("#grid").jsGrid("cancelEdit");
                            return
                        }

                        console.log("updateItem", item)
                        if (item) {
                            return $.ajax({
                                url: "api/roles/" + item.id, data: item, type: "POST"
                            }).then(() => location.reload());
                        }
                    },

                    deleteItem: function (item) {
                        if (item.id <= 3) {
                            $("#grid").jsGrid("cancelEdit");
                            return
                        }

                        if (confirm("Do you really want to delete the role?")) {
                            return $.ajax({
                                type: "DELETE", url: "api/roles/" + item.id, data: item
                            }).then(() => location.reload());
                        }
                    },

					loadData: function () {
						console.log("loadData:")
                        return loadData().then(([_,roles]) => roles)
					},
                },
            })
        })
})

