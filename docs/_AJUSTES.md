- Moderador pode ver e editar eventos, mas não pode criar (policy UI).                                                                                                                                    
- Lista de eventos para admin/moderator mostra todos do tenant; para outros, só os próprios.                                                                                                                
- O botão “Novo evento” fica oculto/desabilitado para moderador.

- Usuários: moderador continua vendo apenas members (já existia em UsersController).
- Criação de usuário agora não exige senha no form nem na validação.
- Se o email já existir, o usuário é associado ao tenant (se não estiver) e recebe o convite.                                                                                                               
- Se não existir, cria com senha aleatória segura e envia convite.                                                                                                                                          
- Email usa template markdown com texto “Crie sua senha / Definir senha” e link do Fortify (password.reset).   
[FIX]
- Adicionei SubstituteBindings::class no middleware group de routes/admin.php para forçar o model binding nas rotas /admin/usuarios/*.
