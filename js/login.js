$(document).ready(function(){
    $('#login-form').submit(function(e){
        e.preventDefault();

        var email=$('#email').val().trim();
        var password=$('#password').val();

        if(email==''||password==''){
            Toast.error('Please fill in all fields', 'Missing Information');
            return;
        }

        var emailRegex=/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if(!emailRegex.test(email)||email.length>50){
            Toast.error('Please enter a valid email address', 'Invalid Email');
            return;
        }

        if(password.length<8){
            Toast.error('Password must be at least 8 characters long', 'Invalid Password');
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
                    Toast.success(response.message, 'Welcome back!');
                    setTimeout(function(){
                        if(response.redirect){
                            window.location.href=response.redirect;
                        }
                    }, 1000);
                }else{
                    Toast.error(response.message, 'Login Failed');
                }
            },
            error:function(xhr,status,error){
                submitBtn.prop('disabled',false);
                submitBtn.html(originalText);
                console.error('Login error:',error);
                Toast.error('Unable to connect to server. Please try again.', 'Connection Error');
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
