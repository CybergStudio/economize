const BASE_URL = "http://localhost/economize/";
const BASE_URL2 = "http://localhost/economize/__system__/";
const BASE_URL3 = "http://localhost/economize/__system__/admin_area/imagens_produtos/";

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 1000
});

function loadingRes(message="") {
    return "<p class='p-loading'><i class='fa fa-circle-notch fa-spin'></i> &nbsp;"+message+"</p>";
}

function clearErrors() {
    $(".has-error").removeClass("has-error");
    $(".help-block").html("");
    $(".help-block-login").html("");
}

function showErrors(error_list) {
    clearErrors();
    $.each(error_list, function(id, message) {
        $(id).parent().siblings(".help-block").html(message);
    })
}

function messages() {
    $.ajax({
        dataType: 'json',
        url: 'functions/messages.php',
        success: function(json) {
            if(json["message"]) {
                Swal.fire({title: json["title"],
                    text: json["text"],
                    type: "warning",
                    showCancelButton: false,
                    confirmButtonColor: "#d9534f",
                    confirmButtonText: "Ok",
                });
            }
        }
    });
}