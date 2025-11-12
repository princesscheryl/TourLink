$(document).ready(function(){
    // Initialize country dropdown only
    $("#country").countrySelect({
        defaultCountry:"gh",
        preferredCountries:['gh','ng','us','gb'],
        responsiveDropdown:true
    });

    $('#register-form').submit(function(e){
        e.preventDefault();

        var full_name=$('#full_name').val().trim();
        var email=$('#email').val().trim();
        var password=$('#password').val();
        var country=$('#country').countrySelect("getSelectedCountryData").name;
        var city=$('#city').val().trim();
        var contact=$('#contact').val().trim();
        var role=$('input[name="role"]:checked').val()||2;

        if(full_name==''||email==''||password==''||!country||city==''||contact==''){
            Swal.fire({icon:'error',title:'Oops...',text:'Please fill in all fields!'});
            return;
        }
        
        if(!/^[a-zA-Z\s\-']{2,100}$/.test(full_name)){
            Swal.fire({icon:'error',title:'Oops...',text:'Please enter a valid name (2-100 characters)'});
            return;
        }
        
        var emailRegex=/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if(!emailRegex.test(email)||email.length>50){
            Swal.fire({icon:'error',title:'Oops...',text:'Please enter a valid email address'});
            return;
        }
        
        if(password.length<8||password.length>150){
            Swal.fire({icon:'error',title:'Oops...',text:'Password must be between 8 and 150 characters'});
            return;
        }
        
        if(country.length>30){
            Swal.fire({icon:'error',title:'Oops...',text:'Country name too long'});
            return;
        }
        
        if(!/^[a-zA-Z\s\-]{2,30}$/.test(city)){
            Swal.fire({icon:'error',title:'Oops...',text:'Please enter a valid city (2-30 characters, letters only)'});
            return;
        }
        
        if(!/^[\d\s\+\-\(\)]{7,15}$/.test(contact)){
            Swal.fire({icon:'error',title:'Oops...',text:'Please enter a valid phone number'});
            return;
        }

        $.ajax({
            url:'../actions/register_user_action.php',
            type:'POST',
            dataType:'json',
            data:{
                full_name:full_name,
                email:email,
                password:password,
                country:country,
                city:city,
                contact:contact,
                role:role
            },
            success:function(response){
                if(response.status==='success'){
                    Swal.fire({icon:'success',title:'Success!',text:response.message}).then((result)=>{
                        if(result.isConfirmed&&response.redirect){
                            window.location.href=response.redirect;
                        }
                    });
                }else{
                    Swal.fire({icon:'error',title:'Registration Failed',text:response.message});
                }
            },
            error:function(xhr,status,error){
                console.error('Registration error:',error);
                Swal.fire({icon:'error',title:'Connection Error',text:'Unable to connect to server. Please try again.'});
            }
        });
    });
});