<?xml version='1.0' encoding='utf-8'?>
<!--
XSL to convert Google Earth KML export to PorPOIse XML
Adam Moore - moore.adam@gmail.com
Centre for Geospatial Science
University of Nottingham
December 2009
Version 0.2
-->

<xsl:stylesheet version='1.0' 
	xmlns:xsl='http://www.w3.org/1999/XSL/Transform'
    xmlns:exsl="http://exslt.org/common"
    extension-element-prefixes="exsl"
	>

<xsl:output method='xml' version='1.0' encoding='utf-8' indent='yes'/>

<!-- Params -->
<xsl:param name="srcLang" select="'EN'"/>
<xsl:param name="targetLang" select="'NL'"/>

<!-- Variables + settings -->
<xsl:variable name="attribution">© Berlitz Publishing</xsl:variable>
<xsl:variable name="audioBaseUrl">http://www.worldservr.com/audio/</xsl:variable>
<xsl:variable name="htmlBaseUrl">http://www.worldservr.com/detail/</xsl:variable>
<xsl:variable name="htmlBasePath">./deployment/</xsl:variable>

<xsl:variable name="phrases" select="document('EN_NL.xml')"/>

<xsl:template match="Country/RegionCity">
<xsl:comment>Produced using langenscheidt-travel.xsl</xsl:comment>
  <pois>
  <!-- Placemark is the only KML attribute processed so far -->
  <xsl:for-each select="InfoItem">
   <xsl:if test="string-length(ItemAdress/GeoPositionLatitude)">
	  <poi>
		<xsl:variable name="detailHref">
			<xsl:value-of select="concat($srcLang, '_', $targetLang, '/', generate-id(.), '.html')"/>
		</xsl:variable>

	   <!-- ID is the position in the Placemark List -->
	   <id><xsl:number value ="position()"/></id>
	   <!-- Title is the KML Name -->
	   <title><xsl:value-of select="@Name" /></title>
	   <line2><xsl:value-of select="@CategorieName" /></line2>
	   <line3><xsl:value-of select="ItemAdress/Street" /></line3>
	   <line4><xsl:value-of select="ItemAdress/ZipCode" /><xsl:text> </xsl:text><xsl:value-of select="ItemAdress/Locality" /></line4>
	   <!-- Lat and Lon are the first 2 strings of Point/coordinates -->
	   <lat><xsl:value-of select="ItemAdress/GeoPositionLatitude" /></lat>
	   <lon><xsl:value-of select="ItemAdress/GeoPositionLongitude" /></lon>
	   <!-- Attribution set to the variable declared at the start -->
	   <attribution>
	   	<xsl:if test="Rating/@Budget">
	   		<xsl:text>Budget: </xsl:text>
			<xsl:call-template name="ratings">
				<xsl:with-param name="rating" select="number(Rating/@Budget)"/>
			</xsl:call-template>
		</xsl:if>
		</attribution>
   		
	   <!-- Currently every Placemark is output as type=1 -->
	   <type>
			<xsl:apply-templates select="." mode="type">
				<xsl:with-param name="category" select="@CategorieCode"/>
			</xsl:apply-templates>
	   </type>
	   <action>
	   	<uri><xsl:value-of select="concat($htmlBaseUrl, '/', $detailHref)"/></uri>
		<label>Detail Info</label>
	   </action>
	   <xsl:if test="string-length(ItemAdress/PhoneNumber)">
		   <action><uri>tel:<xsl:value-of select="ItemAdress/PhoneNumber"/></uri><label>Call Phone</label></action>
	   </xsl:if>
	   <xsl:if test="string-length(ItemAdress/EMail)">
		   <action><uri>mailto:<xsl:value-of select="ItemAdress/EMail"/></uri><label>Send an email</label></action>
	   </xsl:if>
	   <xsl:if test="string-length(ItemAdress/URL)">
		   <action><uri><xsl:value-of select="ItemAdress/URL"/></uri><label>Go to web page</label></action>
	   </xsl:if>
	   <xsl:apply-templates select="$phrases">
			<xsl:with-param name="category" select="@CategorieCode"/>
		</xsl:apply-templates>
		
		<xsl:call-template name="detail">
			<xsl:with-param name="detailHref" select="$detailHref"/>
		</xsl:call-template>
	  </poi>
   </xsl:if>
  </xsl:for-each>
  </pois>
</xsl:template>



<!-- HTML pages for detail view -->
<xsl:template name="detail">
	<xsl:param name="detailHref"/>
	<xsl:variable name="file">
		<xsl:value-of select="concat($htmlBasePath, $detailHref)"/>
	</xsl:variable>
	<exsl:document method="html"
		href="{$file}"
		omit-xml-declaration="yes"
	>
		<xsl:value-of select="ItemText"/>
	</exsl:document>
</xsl:template>

<xsl:template match="InfoItem" mode="type">
	<xsl:param name="category" select="''"/>
	<xsl:choose>
		<xsl:when test="($category='820')">
			<!-- 820: Restaurants -->
			<xsl:text>1</xsl:text>
		</xsl:when>
		<xsl:when test="($category='789')">
			<!-- 789: Shopping -->
			<xsl:text>2</xsl:text>
		</xsl:when>
		<xsl:when test="($category='821')">
			<!-- 821: Places worth seeing -->
			<xsl:text>3</xsl:text>
		</xsl:when>
		<xsl:when test="($category='806')">
			<!-- 806: Hotels -->
			<xsl:text>4</xsl:text>
		</xsl:when>
		<xsl:when test="($category='830')">
			<!-- 830: Entertainment & Night Life -->
			<xsl:text>5</xsl:text>
		</xsl:when>
		<xsl:when test="($category='778')">
			<!-- 778: Tourist Office -->
			<xsl:text>6</xsl:text>
		</xsl:when>
	</xsl:choose>
</xsl:template>


<xsl:template match="MiniPhraseBook">
	<xsl:param name="category" select="''"/>
	<xsl:param name="lang" select="'NL'"/>
	
	<xsl:for-each select="//WordEntry">
		<xsl:choose>
			<xsl:when test="($category='820') and (Category[@SequenceNo='885'] or Category[@SequenceNo='884'])">
				<xsl:apply-templates select="."/>
			</xsl:when>
			<xsl:when test="($category='789') and (Category[@SequenceNo='883'])">
				<xsl:apply-templates select="."/>
			</xsl:when>
			<xsl:when test="($category='821') and (Category[@SequenceNo='882'])">
				<xsl:apply-templates select="."/>
			</xsl:when>
			<xsl:when test="($category='806') and (Category[@SequenceNo='887'])">
				<xsl:apply-templates select="."/>
			</xsl:when>
			<xsl:when test="($category='778') and (Category[@SequenceNo='888'])">
				<xsl:apply-templates select="."/>
			</xsl:when>
			<xsl:when test="($category='830') and (Category[@SequenceNo='877'] or Category[@SequenceNo='879'])">
				<xsl:apply-templates select="."/>
			</xsl:when>
		</xsl:choose>
	</xsl:for-each>

</xsl:template>

<xsl:template match="WordEntry">
	<action>
		<uri>audio://<xsl:value-of select="concat($audioBaseUrl, substring-after(TargetLanguage[@LanguageCode='NL']/File, '..\audio\'))"/>"</uri>
		<label><xsl:value-of select="BaseLanguage[@LanguageCode='EN']/Word"/></label>
	</action>
</xsl:template>

<xsl:template name="ratings">
	<xsl:param name="rating" value="0"/>

	<xsl:if test="$rating > 0">
		<xsl:text>✩</xsl:text>
		<xsl:call-template name="ratings">
			<xsl:with-param name="rating" select="$rating - 1"/>
		</xsl:call-template>
	</xsl:if>
</xsl:template>

</xsl:stylesheet>
