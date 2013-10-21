<?xml version="1.0" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:import href="../../../core/constants.xsl"/>


	<xsl:template match ="/">
		<xsl:call-template name="edit.project" />
	</xsl:template>


	<xsl:template name="edit.project">
		<div id='projectPanel'>
			<div id='editProjectTabs'>
				<ul>
					<li style="margin-left: 30px;"><img style='float: left;' width='16' height='16' src="{$cs-images-icons}projects.png" />Project info</li>
					<li><img style='float: left;' width='16' height='16' src="{$cs-images-icons}teams.png" />Teams</li>
					<li><img style='float: left;' width='16' height='16' src="{$cs-images-icons}itinerary.png" />Itinerary</li>
					<li><img style='float: left;' width='16' height='16' src="{$cs-images-icons}risk.png" />Risk Assessment</li>
					<li><img style='float: left;' width='16' height='16' src="{$cs-images-icons}documents.png" />Documents</li>
				</ul>
				<xsl:call-template name="edit.project.info" />
				<xsl:call-template name="edit.project.team" />
				<xsl:call-template name="edit.project.itinerary" />
				<xsl:call-template name="edit.project.risk.assessment" />
				<!--<xsl:call-template name="edit.project.security.plan" />-->
				<xsl:call-template name="edit.project.documents" />
			</div>
			<div id='projectProgress'>
				<xsl:call-template name="edit.project.progress.panel" />
			</div>
		</div>
	</xsl:template>

	<xsl:template name="edit.project.progress.panel">
		<div></div>
	</xsl:template>

	<xsl:template name="edit.project.info">
			<div class="section" style="padding: 10px;">
				<div>
					<p>Please complete the following information to create a new project</p>
				</div>
				<form class="cartesius">
                <div id="projectInfo">
					<div>
						<label for="projectTitle">Project title</label>
						<input type="text" id="projectTitle">
							<xsl:attribute name='data-bind'>jqxInput: <![CDATA[{width: '200px', height: '25px', value: title, theme: 'metro'}]]></xsl:attribute>	
						</input>
						<!--<div>Explanation</div>-->
					</div>
					<div>
						<label for="projectAuthors">Project authors	</label>
						<div id='projectAuthors'>
							<xsl:attribute name='data-bind'>jqxDropDownList: <![CDATA[{}]]></xsl:attribute>	
						</div>
					</div>
					<div>
						<label for="projectDate">Date</label>
						<div id="projectDate">
							<xsl:attribute name='data-bind'>jqxDateTimeInput: <![CDATA[{width: '200px', height: '25px', value: date, theme: 'metro'}]]></xsl:attribute>	
						</div>
					</div>
					<div>
						<label for="projectReviewDate">Date of next review</label>
						<div id="projectReviewDate">
							<xsl:attribute name='data-bind'>jqxDateTimeInput: <![CDATA[{width: '200px', height: '25px', value: reviewdate,theme: 'metro'}]]></xsl:attribute>	
						</div>
					</div>
					<div>
						<label for="projectSummary">Summary</label>
						<textarea class="jqx-input-content-metro jqx-input-metro" id="projectSummary" />
					</div>
					
<!--

Brief summary of the Plan: main threats, main risk reduction measures, main advice

Relating to the entire NRO or a specific part of the country/countries or a specific activity/campaign?
Security authority, responsibility, task division
Person responsible for updating the plan
Target group – for who is the plan written?
Briefing procedures for staff, visitors, etc.
Other relevant documents/tools, i.e. security policy, travel guidelines
-->
				</div>
				</form>
			</div>
	</xsl:template>

	<xsl:template name="edit.project.itinerary">
			<div class="section">
                <div id="projectItinerary">

				</div>
			</div>
	</xsl:template>

	<xsl:template name="edit.project.team">
			<div class="section">
                <div id="projectTeam">

				</div>
			</div>
	</xsl:template>

	<xsl:template name="edit.project.risk.assessment">
			<div class="section">
                <div id="projectRiskAssessment">



				</div>
			</div>
	</xsl:template>

	<xsl:template name="edit.project.security.plan">
			<div class="section">
                <div id="projectSecurityPlan">


				</div>
			</div>
	</xsl:template>

	<xsl:template name="edit.project.documents">
			<div class="section">
                <div id="projectDocuments">

				</div>
			</div>
	</xsl:template>
	
	<xsl:template match="text()" />

</xsl:stylesheet>
