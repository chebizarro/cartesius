<?xml version="1.0" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template match="/">
		<xsl:apply-templates />
	</xsl:template>

	<xsl:template match="workbench/workbench">
		<div title="workspace" id="{name}" collapsible="true" closable="true" style="height:200px">
			<table class="easyui-datagrid" style="width:700px;height:220px"
					data-options="url:'workspace/layers/{id}',fitColumns:true,nowrap:false,rownumbers:false,showFooter:false,showHeader:false">
				<thead>
					<tr>
						<th data-options="field:'name',width:80">Name</th>
					</tr>
				</thead>
			</table>
		</div>
	</xsl:template>
	
	<xsl:template match="text()" />

</xsl:stylesheet>
