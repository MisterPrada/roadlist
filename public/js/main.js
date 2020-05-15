$(function() {

function preloader_show(){
    $('#btn_submit').css('display', 'none');
    $('#preloader').css('display', 'inline-block');
}

function preloader_hide(){
    $('#btn_submit').css('display', 'inline-block');
    $('#preloader').css('display', 'none');
}

$('#getRoadList').on('submit', function(e){
    e.preventDefault();

    let register = this.register.files[0];

    let formData = new FormData();

    formData.append('organization', this.organization.value);
    formData.append('first_customer', this.first_customer.value);
    formData.append('first_mileage', this.first_mileage.value);
    formData.append('price_type', this.price_type.value);
    formData.append('price', this.price.value);
    formData.append('tax_user', this.tax_user.value);
    formData.append('exp_user', this.exp_user.value);
    formData.append('register', register);


    preloader_show();
    axios.post('/get_road_list', formData,{
        responseType: 'blob', // important
    }).then((response) => {
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'RoadList.zip'); //or any other extension
        document.body.appendChild(link);
        link.click();
        preloader_hide();
    }).catch((err) => {
        preloader_hide();
    });
});



});