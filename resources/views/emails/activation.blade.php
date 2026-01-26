@component('mail::message')
# Olá {{ $user->name }}!

Foi criada uma conta para si no **Asset Management System**.

Para ativar a sua conta e definir a password, clique no botão abaixo:

@component('mail::button', ['url' => $activationUrl, 'color' => 'primary'])
Ativar Conta & Definir Password
@endcomponent

**Duração do link:** 48 horas  
**Email da conta:** {{ $user->email }}

Se não solicitou esta conta, pode ignorar este email com segurança.

**Dicas de segurança:**
- Use uma password forte com mínimo de 8 caracteres
- Inclua letras maiúsculas, minúsculas, números e símbolos
- Não partilhe a sua password com ninguém

Atenciosamente,  
*Equipa do {{ config('app.name') }}*

@component('mail::subcopy')
Se tiver problemas ao clicar no botão "Ativar Conta", copie e cole o seguinte URL no seu navegador:  
[{{ $activationUrl }}]({{ $activationUrl }})
@endcomponent
@endcomponent