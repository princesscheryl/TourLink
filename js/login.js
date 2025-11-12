$(document).ready(function(){
    $('#login-form').submit(function(e){
        e.preventDefault();
        
        var email=$('#email').val().trim();
        var password=$('#password').val();
        
        if(email==''||password==''){
            Swal.fire({icon:'error',title:'Oops...',text:'Please fill in all fields!'});
            return;
        }
        
        var emailRegex=/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if(!emailRegex.test(email)||email.length>50){
            Swal.fire({icon:'error',title:'Invalid Email',text:'Please enter a valid email address'});
            return;
        }
        
        if(password.length<8){
            Swal.fire({icon:'error',title:'Invalid Password',text:'Password must be at least 8 characters long'});
            return;
        }
        
        var submitBtn=$(this).find('button[type="submit"]');
        var originalText=submitBtn.html();
        submitBtn.prop('disabled',true);
        submitBtn.html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>Logging in...');
        
        $.ajax({
            url:'../actions/login_customer_action.php',
            type:'POST',
            dataType:'json',
            data:{email:email,password:password},
            success:function(response){
                submitBtn.prop('disabled',false);
                submitBtn.html(originalText);
                
                if(response.status==='success'){
                    Swal.fire({
                        icon:'success',
                        title:'Success!',
                        text:response.message,
                        timer:1500,
                        showConfirmButton:false
                    }).then((result)=>{
                        if(response.redirect){
                            window.location.href=response.redirect;
                        }
                    });
                }else{
                    Swal.fire({icon:'error',title:'Login Failed',text:response.message});
                }
            },
            error:function(xhr,status,error){
                submitBtn.prop('disabled',false);
                submitBtn.html(originalText);
                console.error('Login error:',error);
                Swal.fire({icon:'error',title:'Connection Error',text:'Unable to connect to server. Please try again.'});
            }
        });
    });
    
    $('#email,#password').keypress(function(e){
        if(e.which==13){
            $('#login-form').submit();
        }
    });
    
    if($('#password').length){
        $('#password').parent().css('position','relative');
        $('#password').after('<i class="fa fa-eye password-toggle" style="position:absolute;right:15px;top:38px;cursor:pointer;color:#b77a7a;z-index:10;"></i>');
        
        $('.password-toggle').click(function(){
            var passwordField=$('#password');
            var type=passwordField.attr('type')==='password'?'text':'password';
            passwordField.attr('type',type);
            $(this).toggleClass('fa-eye fa-eye-slash');
        });
    }
});