param (
    [string]$cmd
)

# 1. Enviar arquivos do tema atualizados para o servidor
Write-Host "Sincronizando tema com a Hostinger..." -ForegroundColor Cyan
scp -r * hostinger:domains/viniciuscodes.com.br/public_html/wp-content/themes/viniciuscodes/

# 2. Garantir permissões de leitura pública em pastas e arquivos
Write-Host "Ajustando permissões dos arquivos..." -ForegroundColor Cyan
ssh hostinger "find domains/viniciuscodes.com.br/public_html/wp-content/themes/viniciuscodes/ -type d -exec chmod 755 {} + && find domains/viniciuscodes.com.br/public_html/wp-content/themes/viniciuscodes/ -type f -exec chmod 644 {} +"

# 3. Executar comando WP-CLI remoto (ou flush de cache por padrao)
if ($cmd) {
    Write-Host "Executando: wp $cmd" -ForegroundColor Yellow
    ssh hostinger "wp --path=domains/viniciuscodes.com.br/public_html $cmd"
} else {
    Write-Host "Limpando cache do WordPress..." -ForegroundColor Green
    ssh hostinger "wp --path=domains/viniciuscodes.com.br/public_html cache flush"
}
