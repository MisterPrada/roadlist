$(function() {

$('#getRoadList').on('submit', function(e){
    e.preventDefault();

    let register = this.register.files[0];

    let formData = new FormData();

    formData.append('register', register);

    axios.post('/get_road_list', formData,{
        responseType: 'blob', // important
    }).then((response) => {
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', 'RoadList.zip'); //or any other extension
        document.body.appendChild(link);
        link.click();
    });
});



});