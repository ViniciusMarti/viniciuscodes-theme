# como usar o git deste tema

O repositorio remoto mora no proprio servidor, em
`/home/u903983301/repos/viniciuscodes-theme.git`. Nao depende de GitHub nem de
servico de fora.

## o dia a dia

Antes de mexer em qualquer arquivo do tema, veja se ha coisa pendente:

    git status

Depois de mexer:

    git add -A
    git commit -m "o que mudou e por que"
    git push

## desfazer

Descartar o que mudou em um arquivo, voltando para o ultimo commit:

    git checkout -- caminho/do/arquivo.php

Ver o que mudou, linha por linha:

    git diff

Voltar o tema inteiro para o ultimo commit:

    git checkout -- .

## se algo der muito errado

O repositorio guarda todos os commits. Para voltar a um estado anterior:

    git log --oneline          # lista os commits
    git checkout <hash> -- .   # traz os arquivos daquele commit

## o que NAO versionar

O `.gitignore` ja exclui o `error_log` e o lixo de sistema. Nao acrescente nada
que seja gerado: cache, minificado, upload. O tema nao tem nada disso hoje.

## fim de linha

O `.gitattributes` tem `* -text`, que desliga a normalizacao. Alguns arquivos do
tema sao CRLF e outros LF, de proposito. Nao mexa nessa regra sem converter os
arquivos antes, ou o historico vira uma bagunca de 12 mil linhas.

## uma copia fora do servidor

Enquanto o repositorio remoto for so o do servidor, o historico vive em um disco
so. Para ter copia fora, crie um repositorio no GitHub e:

    git remote add github git@github.com:ViniciusMarti/<nome>.git
    git push github main
