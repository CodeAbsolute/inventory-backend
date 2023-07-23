
Hi {{$data['email']}},
<p>
    You are receiving this email because we received a password reset request for your account.
      Please click on the below link to reset your password
</p> 
{{$url = $data['url']}}
<a h ref= {{$url}} style="text-decoration: underline;" ></a>

