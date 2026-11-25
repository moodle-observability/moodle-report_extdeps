moodle-report_extdeps
=====================

**Relatórios das dependências externas dos cursos do Moodle.**

Mostra o que o ``tool_extdeps`` encontrou: os vídeos e pacotes quebrados de cada curso, o que fazer com cada um e um
botão que abre a página onde ele pode ser corrigido.

.. note::

   Versão 0.1.2 (alfa).

Recursos
--------

* **Visão do site**: cartões por provedor e lista de cursos com as ocorrências quebradas, filtros e exportação (CSV,
  Excel, ODS e outros formatos).
* **Todas as ocorrências**: cada lugar onde uma dependência é usada, só as quebradas por padrão.
* **Visão do curso** (para os professores editores): seção, atividade, provedor, situação, desde quando e
  **Editar**, que abre a atividade, o capítulo do livro, a página da lição, as questões do questionário, a seção ou o
  curso.
* **Página da dependência**: evidência da última verificação, onde é usada, histórico da situação e falso positivo.
* **Verificar agora**: uma atividade é verificada na hora; um curso, um provedor ou o site, em segundo plano, com
  página de resultado que se atualiza sozinha.
* **Relatórios personalizados**: fonte "Dependências externas" do Report Builder.

Requisitos
----------

* Moodle 4.5 ou 5.3 (LTS).
* ``tool_extdeps`` 0.1.2 ou superior.

.. toctree::
   :maxdepth: 1

   installation
   configuration
   usage
