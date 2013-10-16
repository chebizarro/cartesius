<?xml version="1.0" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:import href="constants.xsl"/>
	<xsl:import href="geo.head.xsl"/>
	<xsl:import href="ui.head.xsl"/>
	<xsl:import href="framework.head.xsl"/>
	<xsl:import href="edit.project.xsl"/>

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
		<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0" />

		<!--<meta http-equiv="refresh" content="20" />-->

		<xsl:call-template name="framework.head" />
		<xsl:call-template name="ui.head" />
		<xsl:call-template name="geo.head" />

		<!--<script type="text/javascript" src="{$cs-scripts}app.js"></script>-->
		<!--<link rel="stylesheet" type="text/css" href="{$cs-styles}app.css" />-->
		
	</head>
	</xsl:template>
	
	<xsl:template name="body">
		<body class='default'>
			<div id="mainSplitter">
				
				<div class="splitter-panel">
					<xsl:call-template name="header"/>
				</div>
				
				<div class="splitter-panel">
				
					<div id="workbenchSplitter">
				
						<div class="splitter-panel">
							<xsl:call-template name="workbench"/>
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
		</body>		
	</xsl:template>

	<xsl:template name="header">
		<div id='header'>
			<div>
				<img style='margin: 2px; vertical-align:middle;' src="{$cs-images-icons}icon.png" alt="User" />
				<span><strong>Cartesius</strong></span>
				<img style='margin: 2px; vertical-align:middle;float:right' src="{$cs-images-icons}user_blank.png" alt="User" />
				<img style='margin: 2px; vertical-align:middle;float:right' src="{$cs-images-icons}config.png" alt="Preferences" />
				<img style='margin: 2px; vertical-align:middle;float:right' src="{$cs-images-icons}logout.png" alt="Logout" />
			</div>
		</div>
	</xsl:template>

	<xsl:template name="workbench">
		<div style="border: none;">
			<!-- Navigation bar-->
			<div class="jqx-hideborder jqx-hidescrollbars">
				<div id="navigationBar">
					<!-- People -->
					<div>
						<div>
							<div style='float: left;'>
								<img alt='People' src='{$cs-images-icons}people.png' />
							</div>
							<div style='margin-left: 4px; float: left;'>People</div>
						</div>
					</div>
					<div>
						<div id="peopleMenu" style="padding:2px;">
							<a href="" id="addPerson"><img style='float: left; margin-left:2px;' src='{$cs-images-icons}add.png' /></a>
							<a href="" id="deletePerson"><img style='float: left; margin-left:2px;' src='{$cs-images-icons}delete.png' /></a>
							<img style='float: left; margin-left:2px;' src='{$cs-images-icons}config.png' />
							<img style='float: left; margin-left:2px;' src='{$cs-images-icons}location.png' />
						</div>
						<div id="peopleListbox">
						</div>
					</div>
					<!-- Teams -->
					<div>
						<div>
							<div style='float: left;'>
								<img alt='Teams' src='{$cs-images-icons}teams.png' />
							</div>
							<div style='margin-left: 4px; float: left;'>Teams</div>
						</div>
					</div>
					
					<div>

					</div>

					<!-- Projects -->
					<div>
						<div>
							<div style='float: left;'>
								<img alt='Projects' src='{$cs-images-icons}checklist.png' />
							</div>
							<div style='margin-left: 4px; float: left;'>Projects</div>
						</div>
					</div>
					
					<div>

					</div>
					<!-- Layers -->
					<div>
						<div>
							<div style='float: left;'>
								<img alt='Layers' src='{$cs-images-icons}layers.png' />
							</div>
							<div style='margin-left: 4px; float: left;'>Layers</div>
						</div>
					</div>
					
					<div>
						<div id="layersList">
						</div>
					</div>
					
				</div>

			</div>
		</div>
	</xsl:template>

	<xsl:template name="workspace">
		<div id="workspace">
			<div id="map" style="width: '100%; height: '100%;"></div>
			
			<div id="portalWindow">
                <div id="customWindowHeader">
                    <span id="captureContainer" style="float: left">Project </span>
                </div>
                <div id="customWindowContent" style="overflow: hidden">
                    <div style="margin: 10px">
						<xsl:call-template name="edit.project" /> 
					</div>
                </div>
            </div>
			
		</div>
	</xsl:template>

	<xsl:template name="feed">
		<div id="feed">
		</div>
	</xsl:template>

	
	<xsl:template match="text()" />
	
</xsl:stylesheet>
