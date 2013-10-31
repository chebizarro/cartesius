<?xml version="1.0" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:param name="cs-scripts">/scripts/</xsl:param>
	<xsl:param name="cs-scripts-lib">/scripts/lib/</xsl:param>
	<xsl:param name="cs-styles">/style/</xsl:param>
	<xsl:param name="cs-images">/images/</xsl:param>
	<xsl:param name="cs-images-icons">/images/icons/</xsl:param>

	<xsl:output method="html" version="4" encoding="UTF-8" indent="yes" />
	
	<xsl:template match ="/">
		<xsl:text disable-output-escaping='yes'>&lt;!DOCTYPE html></xsl:text>
		<html>
			<xsl:call-template name="head" />
			<xsl:call-template name="body" />
		</html>
	</xsl:template>
	
	<xsl:template name="head">
	<head>
		<title>Cartesius</title>
		<link rel="shortcut icon" href="{$cs-images-icons}cartesius.ico" />	
		<link rel="stylesheet" href="./styles/normalize.css" type="text/css" />
		<link rel="stylesheet" href="./styles/cartesius.css" type="text/css" />
	
		<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0" />
		
		<xsl:text disable-output-escaping="yes">&lt;!--[if lt IE 9]&gt;
		&lt;script type="text/javascript" charset="utf-8" src="http://html5shim.googlecode.com/svn/trunk/html5.js"&gt;&lt;/script&gt;
		&lt;script type="text/javascript" charset="utf-8" src="http://cdnjs.cloudflare.com/ajax/libs/json2/20110223/json2.js"&gt;&lt;/script&gt;
		&lt;script type="text/javascript" charset="utf-8" src="http://explorercanvas.googlecode.com/svn/trunk/excanvas.js"&gt;&lt;/script&gt;
		&lt;![endif]--&gt;</xsl:text>

        <!-- we use jquery and underscore as 2 utilities in the BoilerplateJS core -->
        <script src="./lib/breeze/Scripts/q.min.js" type="text/javascript" charset="utf-8"></script>
        <script src="./lib/jquery/jquery-1.10.2.js" type="text/javascript" charset="utf-8"></script>
        <script src="./lib/jquery/jquery-migrate-1.2.1.js" type="text/javascript" charset="utf-8"></script>
        <script src="./lib/underscore/underscore-1.3.3.js" type="text/javascript" charset="utf-8"></script>
        <!-- following libraries are used by the UrlController for client routing and browser history --> 
        <script src="./lib/signals/signals.min.js" type="text/javascript" charset="utf-8"></script>
        <script src="./lib/crossroads/crossroads.min.js" type="text/javascript" charset="utf-8"></script>
        <script src="./lib/hasher/hasher.min.js" type="text/javascript" charset="utf-8"></script>
        <!-- following library is used by the Mediator class for pub-sub event handling -->
        <script src="./lib/pubsub/pubsub-20120708.js" type="text/javascript" charset="utf-8"></script>
        <!-- BPJS initializer scripts-->
        <script src="./lib/boilerplate/groundwork.js" type="text/javascript" charset="utf-8"></script>

        <!--<script src="./lib/stapling/stapling-1.5.js" type="text/javascript" charset="utf-8"></script>-->

        <script src="./lib/knockout/knockout-2.3.0.js" type="text/javascript" charset="utf-8"></script>
        <script src="./lib/knockout-postbox/build/knockout-postbox.min.js" type="text/javascript" charset="utf-8"></script>


		<script type="text/javascript" src="./lib/jqwidgets/jqwidgets/jqxknockout.js"></script>
		<link rel="stylesheet" href="./lib/jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
		<link rel="stylesheet" href="./lib/jqwidgets/jqwidgets/styles/jqx.bootstrap.css" type="text/css" />
		<link rel="stylesheet" href="./lib/jqwidgets/jqwidgets/styles/jqx.metro.css" type="text/css" />

		<script type="text/javascript" src="./lib/jqwidgets/jqwidgets/jqxcore.js"></script>
		<script type="text/javascript" src="./lib/jqwidgets/jqwidgets/jqxsplitter.js"></script>
        
        <!-- following is the main entry script to the application code. we use requirejs to load main.js -->
        <script type="text/javascript" data-main="./scripts/main.js" src="./lib/require/require.js"></script>
        

        
	</head>
	
	</xsl:template>
	
	<xsl:template name="body">
		<body class='default'>
			<div id="page-content">
			<div id="mainSplitter">
				
				<div class="splitter-panel">
					<xsl:call-template name="header"/>
				</div>
				
				<div class="splitter-panel">
				
					<div id="workbenchSplitter">
				
						<div class="splitter-panel">
							<div class="workbench">
							</div>
						</div>
						
						<div class="splitter-panel">
							
							<div id="workspaceSplitter">
							
								<div class="splitter-panel">
									<xsl:call-template name="workspace"/>
								</div>

								<div class="splitter-panel">
									<xsl:call-template name="feed"/>
								</div>

							</div>				
				
						</div>
				
					</div>
				
				</div>
				</div>
			</div>
		</body>		
	</xsl:template>

	<xsl:template name="header">
		<div id='header'>
			<div>
				<div>
					<img style='margin: 2px; float:left;' src="{$cs-images-icons}icon.png" alt="Cartesius" />
					<!--<span style="vertical-align:middle;"><strong>Cartesius</strong></span>-->
				</div>
				<div id='mainMenu' style='visibility: hidden; margin-left: 5px; float: right;'>
					<xsl:attribute name='data-bind'>jqxMenu: <![CDATA[{source: menu, height: 32, theme: 'metro'}]]></xsl:attribute>
				</div>

				<!--
				<img style='margin: 2px; vertical-align:middle;' src="{$cs-images-icons}icon.png" alt="User" />
				<img style='margin: 2px; vertical-align:middle;float:right'><xsl:attribute name='src'><xsl:value-of select="/root/data/account/image" />?sz=32</xsl:attribute><xsl:attribute name='alt'><xsl:value-of select="/root/data/account/username" /></xsl:attribute></img>
				<img style='margin: 2px; vertical-align:middle;float:right' src="{$cs-images-icons}config.png" alt="Preferences" />
				<a href="/logout"><img style='margin: 2px; vertical-align:middle;float:right' src="{$cs-images-icons}logout.png" alt="Logout" /></a>
				-->
			</div>
		</div>
	</xsl:template>


	<xsl:template name="workspace">
		<div id="workspace" class="appcontent">
		</div>
	</xsl:template>

	<xsl:template name="feed">
		<div id="feed" class="feedpanel">
		</div>
	</xsl:template>

	
	<xsl:template match="text()" />
	
</xsl:stylesheet>
