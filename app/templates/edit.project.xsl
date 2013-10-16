<?xml version="1.0" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:import href="constants.xsl"/>
	<xsl:import href="geo.head.xsl"/>
	<xsl:import href="ui.head.xsl"/>
	<xsl:import href="framework.head.xsl"/>

	<xsl:template match ="/">
		<xsl:call-template name="edit.project" />
	</xsl:template>


	<xsl:template name="edit.project">
		<div id='editProjectTabs'>
			<ul>
                <li style="margin-left: 30px;"><img style='float: left;' width='16' height='16' src="{$cs-images-icons}checklist.png" />Project info</li>
                <li><img style='float: left;' width='16' height='16' src="{$cs-images-icons}itinerary.png" />Itinerary</li>
                <li><img style='float: left;' width='16' height='16' src="{$cs-images-icons}teams.png" />Team</li>
                <li><img style='float: left;' width='16' height='16' src="{$cs-images-icons}risk.png" />Risk Assessment</li>
                <li><img style='float: left;' width='16' height='16' src="{$cs-images-icons}documents.png" />Documents</li>
            </ul>

			<div class="section">
                <div id="projectInfo">
					<input type="text" id="projectTitle" placeholder="Project title"/>

				</div>
			</div>

			<div class="section">
                <div id="projectItinerary">

				</div>
			</div>

			<div class="section">
                <div id="projectTeam">

				</div>
			</div>

			<div class="section">
                <div id="projectRiskAssessment">

				</div>
			</div>

			<div class="section">
                <div id="projectDocuments">

				</div>
			</div>
			
		</div>
	</xsl:template>
	
	<xsl:template match="text()" />

</xsl:stylesheet>
