<?xml version="1.0" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:param name="cs-scripts">/scripts/</xsl:param>
	<xsl:param name="cs-scripts-lib">/scripts/lib/</xsl:param>
	<xsl:param name="cs-styles">/style/</xsl:param>
	<xsl:param name="cs-images">/images/</xsl:param>
	<xsl:param name="cs-images-icons">/images/icons/</xsl:param>

	<xsl:output method="html" version="4" encoding="UTF-8" indent="yes" />
	
	<xsl:template match ="/">
		<html class="panel-fit">
			<xsl:call-template name="head" />
			<xsl:call-template name="body" />
		</html>
	</xsl:template>
	
	<xsl:template name="head">
	<head>
		<title>Cartesius</title>
		<link rel="shortcut icon" href="{$cs-images-icons}cartesius.ico" />		
        
	</head>
	
	</xsl:template>
	
	<xsl:template name="body">
		<body class='default'>
			<div style='margin-left:auto;margin-right:auto;width:33%'>
				<img src='{$cs-images-icons}cartesius_loader.gif'/>
				<a><xsl:attribute name="href"><xsl:value-of select="/root/data/link" /></xsl:attribute><img src='{$cs-images}sign-in-with-google.png'/></a>
			</div>
		</body>		
	</xsl:template>


	
	<xsl:template match="text()" />
	
</xsl:stylesheet>
