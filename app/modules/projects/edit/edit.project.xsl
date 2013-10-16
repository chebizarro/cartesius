<?xml version="1.0" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:import href="/app/core/constants.xsl"/>


	<xsl:template name="text" match="" mode="edit">

		<label for="male">Male</label>
		<input type="text" data-bind="value: textValue" />

	</xsl:template>

	<xsl:template name="checkbox" match="" mode="edit">

		<label for="male">Male</label>
		<input type="checkbox" data-bind="checked: checkboxValue" />

	</xsl:template>

	<xsl:template name="radio" match="" mode="edit">

		<label><input type="radio" name="fruit" value="Apple" data-bind="checked: radioValue" />Apple</label>

	</xsl:template>

	<xsl:template name="select" match="" mode="edit">

		<label for="male">Male</label>
		<select data-bind="source: fruits, value: selectValue"></select>

	</xsl:template>

	<xsl:template name="multiple-select" match="" mode="edit">

		<label for="male">Male</label>
		<select multiple data-bind="source: fruits, value: multipleSelectValue"></select>

	</xsl:template>

	<xsl:template name="autocomplete" match="" mode="edit">

		<label for="male">Male</label>
		<input data-role="autocomplete" data-text-field="name" data-bind="source: colors, value: autoCompleteValue"/>

	</xsl:template>

	<xsl:template name="combobox" match="" mode="edit">

		<label for="male">Male</label>
		<select data-role="combobox"
			data-text-field="name" data-value-field="value" data-bind="source: colors, value: comboBoxValue"></select>

	</xsl:template>

	<xsl:template name="datepicker" match="" mode="edit">

		<label for="male">Male</label>
		<input data-role="datepicker" data-bind="value: datePickerValue" />

	</xsl:template>

	<xsl:template name="dropdownlist" match="" mode="edit">

		<label for="male">Male</label>
		<select data-role="dropdownlist"
			data-text-field="name" data-value-field="value" data-bind="source: colors, value: dropDownListValue"></select>

	</xsl:template>

	<xsl:template name="grid" match="" mode="edit">

		<label for="male">Male</label>
		<div data-role="grid"
			data-sortable="true" data-editable="true"
			data-columns='["Name", "Price", "UnitsInStock", {"command": "destroy"}]'
			data-bind="source: gridSource"></div>

	</xsl:template>

	<xsl:template name="numerictextbox" match="" mode="edit">

		<label for="male">Male</label>
		<input data-role="numerictextbox" data-format="c" data-bind="value: numericTextBoxValue" />

	</xsl:template>

	<xsl:template name="slider" match="" mode="edit">

		<label for="male">Male</label>
		<input data-role="slider" data-bind="value: sliderValue" />

	</xsl:template>

	<xsl:template name="timepicker" match="" mode="edit">

		<label for="male">Male</label>
		<input data-role="timepicker" data-bind="value: timePickerValue" />

	</xsl:template>

	<xsl:template name="treeview" match="" mode="edit">

		<label for="male">Male</label>
		<div data-role="treeview"
			data-animation="false"
			data-drag-and-drop="true"
			data-bind="source: treeviewSource"></div>

	</xsl:template>


<!--
<field id='' type='' ></field>

type	data-bind source name label

	<h4>Slider</h4>
	

	<h4>TabStrip</h4>
	<div data-role="tabstrip" data-animation="false">
		<ul>
			<li class="k-state-active">First</li>
			<li>Second</li>
		</ul>
		<div>
			<h4>First page:</h4>
			Pick a time: <input data-role="timepicker" data-bind="value: timePickerValue"/>
		</div>
		<div>
			<h4>Second page:</h4>
			Time is: <span data-bind="text: displayTimePickerValue"></span>
		</div>
	</div>


		<h4>Splitter</h4>
		<div data-role="splitter" data-panes="[{size:'30%', collapsible:true},{size:'70%'}]">
			<div>Pane 1</div>
			<div>Pane 2</div>
		</div>

-->



	<xsl:template match ="/">
		<xsl:call-template name="edit.project" />
	</xsl:template>


	<xsl:template name="edit.project">
		<div id='projectPanel'>
			<div id='editProjectTabs'>
				<ul>
					<li style="margin-left: 30px;"><img style='float: left;' width='16' height='16' src="{$cs-images-icons}checklist.png" />Project info</li>
					<li><img style='float: left;' width='16' height='16' src="{$cs-images-icons}itinerary.png" />Itinerary</li>
					<li><img style='float: left;' width='16' height='16' src="{$cs-images-icons}teams.png" />Team</li>
					<li><img style='float: left;' width='16' height='16' src="{$cs-images-icons}risk.png" />Risk Assessment</li>
					<li><img style='float: left;' width='16' height='16' src="{$cs-images-icons}documents.png" />Documents</li>
				</ul>
				<xsl:call-template name="edit.project.info" />
				<xsl:call-template name="edit.project.itinerary" />
				<xsl:call-template name="edit.project.team" />
				<xsl:call-template name="edit.project.risk.assessment" />
				<xsl:call-template name="edit.project.security.plan" />
				<xsl:call-template name="edit.project.documents" />
			</div>
			<div id='projectProgress'>
				<xsl:call-template name="edit.project.progress.panel" />
			</div>
		</div>
	</xsl:template>

	<xsl:template name="edit.project.progress.panel">
		
	</xsl:template>

	<xsl:template name="edit.project.info">
			<div class="section">
                <div id="projectInfo">
					<label for=""></label><input type="text" id="projectTitle" placeholder="Project title"/>
					
					
<!--

Author/responsible person 
Date 
Date of next review
Brief summary of the Plan: main threats, main risk reduction measures, main advice

Relating to the entire NRO or a specific part of the country/countries or a specific activity/campaign?
Security authority, responsibility, task division
Person responsible for updating the plan
Target group – for who is the plan written?
Briefing procedures for staff, visitors, etc.
Other relevant documents/tools, i.e. security policy, travel guidelines
-->
				</div>
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
