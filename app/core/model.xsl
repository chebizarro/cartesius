<?xml version="1.0" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="text" omit-xml-declaration="yes" indent="no"/>
	<xsl:template match ="/">
define(function(require) {
	var ViewModel = function (data) {
		self = this;
		<xsl:for-each select="/root/data/information_schema">self.<xsl:value-of select="column_name"/> = ko.observable();
		</xsl:for-each>
		
		$.each( data, function( key, value ) {
			self[key].call(value);
		});
	};
	return ViewModel;
});
	</xsl:template>

	<xsl:template match="text()" />
	
</xsl:stylesheet>
