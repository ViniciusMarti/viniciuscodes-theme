<?php
/**
 * Template Name: Viagens & Lugares
 *
 * A página `/viagens/`: onde morei e por onde passei, organizada em linha do tempo por anos.
 *
 * @package ViniciusCodes
 */

get_header();

$vc_lugares_dados = array(
	// Onde morei
	array( 'data' => '1999',    'local' => 'Guaraíta, GO',          'pais' => 'br', 'tipo' => 'moradia' ),
	array( 'data' => '2005',    'local' => 'Manaus, AM',            'pais' => 'br', 'tipo' => 'moradia' ),
	array( 'data' => '2007',    'local' => 'Porto Velho, RO',       'pais' => 'br', 'tipo' => 'moradia' ),
	array( 'data' => '2008',    'local' => 'Manaus, AM',            'pais' => 'br', 'tipo' => 'moradia' ),
	array( 'data' => '2009',    'local' => 'Guaraíta, GO',          'pais' => 'br', 'tipo' => 'moradia' ),
	array( 'data' => '2010',    'local' => 'Goiânia, GO',           'pais' => 'br', 'tipo' => 'moradia' ),
	array( 'data' => '2022-04', 'local' => 'Curitiba, PR',          'pais' => 'br', 'tipo' => 'moradia' ),

	// Viagens datadas
	array( 'data' => '2012',    'local' => 'São Luís, MA',          'pais' => 'br', 'tipo' => 'viagem' ),
	array( 'data' => '2017',    'local' => 'Curitiba, PR',          'pais' => 'br', 'tipo' => 'viagem' ),
	array( 'data' => '2021',    'local' => 'Curitiba, PR',          'pais' => 'br', 'tipo' => 'viagem' ),
	array( 'data' => '2021-02', 'local' => 'Balneário Camboriú, SC', 'pais' => 'br', 'tipo' => 'viagem' ),
	array( 'data' => '2021-02', 'local' => 'Florianópolis, SC',     'pais' => 'br', 'tipo' => 'viagem' ),
	array( 'data' => '2021-02', 'local' => 'Penha, SC',             'pais' => 'br', 'tipo' => 'viagem' ),
	array( 'data' => '2021-11', 'local' => 'Morretes, PR',            'pais' => 'br', 'tipo' => 'viagem' ),
	array( 'data' => '2022-02', 'local' => 'Sorocaba, SP',          'pais' => 'br', 'tipo' => 'viagem' ),
	array( 'data' => '2022-03', 'local' => 'Vila Velha, ES',           'pais' => 'br', 'tipo' => 'viagem' ),
	array( 'data' => '2022-04', 'local' => 'Vitória, ES',           'pais' => 'br', 'tipo' => 'viagem' ),
	array( 'data' => '2022-06', 'local' => 'Guaratuba, PR',         'pais' => 'br', 'tipo' => 'viagem' ),
	array( 'data' => '2022-07', 'local' => 'São Francisco do Sul, SC', 'pais' => 'br', 'tipo' => 'viagem' ),
	array( 'data' => '2022-09', 'local' => 'Assunção, Paraguai',        'pais' => 'py', 'tipo' => 'viagem' ),
	array( 'data' => '2023-05', 'local' => 'Gramado, RS',           'pais' => 'br', 'tipo' => 'viagem' ),
	array( 'data' => '2023-10', 'local' => 'Florianópolis, SC',     'pais' => 'br', 'tipo' => 'viagem' ),
	array( 'data' => '2024-04', 'local' => 'Belo Horizonte, MG',    'pais' => 'br', 'tipo' => 'viagem' ),
	array( 'data' => '2024-04', 'local' => 'Ouro Preto, MG',         'pais' => 'br', 'tipo' => 'viagem' ),
	array( 'data' => '2024-08', 'local' => 'Balneário Camboriú, SC', 'pais' => 'br', 'tipo' => 'viagem' ),
	array( 'data' => '2024-08', 'local' => 'Florianópolis, SC',     'pais' => 'br', 'tipo' => 'viagem' ),
	array( 'data' => '2024-08', 'local' => 'Penha, SC',             'pais' => 'br', 'tipo' => 'viagem' ),
	array( 'data' => '2025-04', 'local' => 'Guaratuba, PR',         'pais' => 'br', 'tipo' => 'viagem' ),
	array( 'data' => '2025-05', 'local' => 'Penha, SC',             'pais' => 'br', 'tipo' => 'viagem' ),
	array( 'data' => '2025-07', 'local' => 'São Paulo, SP',         'pais' => 'br', 'tipo' => 'viagem' ),
	array( 'data' => '2025-07', 'local' => 'Rio de Janeiro, RJ',      'pais' => 'br', 'tipo' => 'viagem' ),
	array( 'data' => '2025-07', 'local' => 'Niterói, RJ',             'pais' => 'br', 'tipo' => 'viagem' ),
	array( 'data' => '2025-09', 'local' => 'Goiânia, GO',           'pais' => 'br', 'tipo' => 'viagem' ),
	array( 'data' => '2025-11', 'local' => 'Navegantes, SC',        'pais' => 'br', 'tipo' => 'viagem' ),
	array( 'data' => '2025-12', 'local' => 'Castro, PR',            'pais' => 'br', 'tipo' => 'viagem' ),
	array( 'data' => '2026-02', 'local' => 'Santiago, Chile',        'pais' => 'cl', 'tipo' => 'viagem' ),
	array( 'data' => '2027-02', 'local' => 'Calgary, Canadá',         'pais' => 'ca', 'tipo' => 'viagem' ),
);

// Ordena cronologicamente decrescente
usort(
	$vc_lugares_dados,
	static function ( $a, $b ) {
		return strcmp( $b['data'], $a['data'] );
	}
);

// Agrupa por Ano
$vc_por_ano = array();
foreach ( $vc_lugares_dados as $item ) {
	$ano = substr( $item['data'], 0, 4 );
	if ( ! isset( $vc_por_ano[ $ano ] ) ) {
		$vc_por_ano[ $ano ] = array();
	}
	$vc_por_ano[ $ano ][] = $item;
}

// Cidades visitadas sem data exata conhecida
$vc_cidades_sem_data = array(
	array( 'local' => 'Aparecida de Goiânia, GO', 'pais' => 'br' ),
	array( 'local' => 'Britânia, GO',              'pais' => 'br' ),
	array( 'local' => 'Caldas Novas, GO',          'pais' => 'br' ),
	array( 'local' => 'Confins, MG',               'pais' => 'br' ),
	array( 'local' => 'Goiás, GO',                 'pais' => 'br' ),
	array( 'local' => 'Guarulhos, SP',             'pais' => 'br' ),
	array( 'local' => 'Iporá, GO',                 'pais' => 'br' ),
	array( 'local' => 'Itajaí, SC',                'pais' => 'br' ),
	array( 'local' => 'Itapuranga, GO',            'pais' => 'br' ),
	array( 'local' => 'Joinville, SC',             'pais' => 'br' ),
	array( 'local' => 'Ponta Grossa, PR',          'pais' => 'br' ),
	array( 'local' => 'Porto Alegre, RS',          'pais' => 'br' ),
	array( 'local' => 'Recife, PE',                'pais' => 'br' ),
	array( 'local' => 'Rio Verde, GO',             'pais' => 'br' ),
	array( 'local' => 'Santo Antônio do Monte, MG','pais' => 'br' ),
	array( 'local' => 'Trindade, GO',              'pais' => 'br' ),
	array( 'local' => 'Votorantim, SP',            'pais' => 'br' ),
);
usort(
	$vc_cidades_sem_data,
	static function ( $a, $b ) {
		return strcmp( $a['local'], $b['local'] );
	}
);
?>
<article data-page="viagens" class="article-viagens">
	<header class="term-header">
		<h1 class="term-header__name">viagens &amp; lugares</h1>
		<p class="term-header__desc">onde eu morei e por onde passei. a linha do tempo reúne os registros com data; abaixo estão outros destinos que ainda vou datar conforme lembrar.</p>
	</header>

	<section class="travel-timeline-section" aria-label="Linha do tempo de viagens e moradias">
		<?php foreach ( $vc_por_ano as $ano => $itens_do_ano ) : ?>
			<div class="travel-year-block">
				<div class="travel-year-header">
					<span class="travel-year-title"><?php echo esc_html( $ano ); ?></span>
				</div>

				<div class="travel-timeline">
					<?php foreach ( $itens_do_ano as $item ) : ?>
						<?php
						$eh_moradia  = 'moradia' === $item['tipo'];
						$codigo_pais = $item['pais'] ?? 'br';
						$paises_nomes = array( 'br' => 'Brasil', 'cl' => 'Chile', 'py' => 'Paraguai', 'ca' => 'Canadá' );
						$nome_pais   = $paises_nomes[ $codigo_pais ] ?? 'Brasil';
						$flag_url    = get_theme_file_uri( "assets/flags/{$codigo_pais}.svg" );

						// Se tiver mês (AAAA-MM), formata ou exibe o mês
						$data_label = $item['data'];
						if ( strlen( $item['data'] ) === 7 ) {
							$mes_num = substr( $item['data'], 5, 2 );
							$meses_nome = array(
								'01' => 'jan', '02' => 'fev', '03' => 'mar', '04' => 'abr',
								'05' => 'mai', '06' => 'jun', '07' => 'jul', '08' => 'ago',
								'09' => 'set', '10' => 'out', '11' => 'nov', '12' => 'dez',
							);
							$data_label = $meses_nome[ $mes_num ] ?? $mes_num;
						}
						?>
						<div class="travel-timeline__item">
							<div class="travel-timeline__marker" aria-hidden="true">
								<span class="travel-timeline__dot"></span>
							</div>

							<div class="travel-timeline__content">
								<time class="travel-timeline__date"><?php echo esc_html( $data_label ); ?></time>
								<span class="travel-timeline__place">
									<img class="travel-flag" src="<?php echo esc_url( $flag_url ); ?>" alt="Bandeira do <?php echo esc_attr( $nome_pais ); ?>" width="16" height="12" loading="lazy" decoding="async">
									<span><?php echo esc_html( $item['local'] ); ?></span>
								</span>
								<?php if ( $eh_moradia ) : ?>
									<span class="travel-badge travel-badge--home">
										<span class="travel-badge__icon" aria-hidden="true"><?php echo vc_icone( 'house', 12 ); ?></span>
										<span>moradia</span>
									</span>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</section>

	<section class="travel-undated-section" aria-label="Outros lugares visitados">
		<header class="travel-undated-header">
			<h2 class="travel-undated-title">outros lugares visitados</h2>
			<p class="travel-undated-desc">cidades por onde passei e que ainda vou datar:</p>
		</header>

		<div class="travel-tags">
			<?php foreach ( $vc_cidades_sem_data as $cidade_item ) : ?>
				<?php
				$cod_pais  = $cidade_item['pais'] ?? 'br';
				$paises_nomes = array( 'br' => 'Brasil', 'cl' => 'Chile', 'py' => 'Paraguai', 'ca' => 'Canadá' );
				$nome_pais   = $paises_nomes[ $cod_pais ] ?? 'Brasil';
				$flag_url  = get_theme_file_uri( "assets/flags/{$cod_pais}.svg" );
				?>
				<span class="travel-tag">
					<img class="travel-flag" src="<?php echo esc_url( $flag_url ); ?>" alt="Bandeira do <?php echo esc_attr( $nome_pais ); ?>" width="14" height="10.5" loading="lazy" decoding="async">
					<span><?php echo esc_html( $cidade_item['local'] ); ?></span>
				</span>
			<?php endforeach; ?>
		</div>
	</section>
</article>

<?php
get_footer();
