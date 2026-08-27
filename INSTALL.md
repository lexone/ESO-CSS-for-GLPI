# Instalação rápida

```bash
cd /var/www/html/plugins
git clone https://github.com/lexone/ESO-CSS-for-GLPI.git esocss
cd esocss
sudo ./scripts/install.sh /var/www/html
```

Depois acesse **Configurar → Plugins → ESO CSS for GLPI**.

O diretório do plugin precisa se chamar exatamente `esocss`.

## Atualizações futuras

Na configuração do plugin, clique em **Verificar versão**. Se houver uma versão superior, o botão **Atualizar automaticamente** será exibido.

O servidor web precisa ter permissão de escrita em `plugins/esocss` e na pasta `plugins`. Quando o componente Git do GLPI não estiver disponível, o atualizador usa automaticamente o pacote ZIP da release, validado por SHA-256.

### Cloudflare

Se o domínio estiver protegido pelo Cloudflare, limite a exceção de WAF ao hostname do GLPI, a usuários autenticados e a estes caminhos:

```text
/plugins/esocss/front/settings.php
/plugins/esocss/front/settings.form.php
/plugins/esocss/front/update.form.php
```

O caminho antigo `/plugins/esocss/front/config.php` apenas redireciona para a nova interface.

## Imagens da página inicial

Para permitir o upload de fundo e logotipo, confirme que o servidor web pode criar e gravar em:

```text
files/_plugins
```

As imagens serão armazenadas em `files/_plugins/esocss` e permanecerão no servidor durante atualizações automáticas do plugin.
