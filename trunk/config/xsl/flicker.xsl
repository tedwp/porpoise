<?xml version="1.0" encoding="UTF-8"?>
<!-- courtesy of Guillaume Danielou -->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">  
    
    <xsl:template match="rsp/photos">                     
            <pois>
                <xsl:for-each select="photo">                        
                    <poi>
                        
                        <!--  -->
                        <id><xsl:value-of select="@id"/></id>
                        
                        <!-- Flickr title: [title] -->
                        <title><xsl:value-of select="@title"/></title>
                        
                        <!-- Flickr user: [ownername] -->
                        <line2><xsl:value-of select="@ownername"/></line2>                        
                        
                        <!-- Flickr date: [date_taken] -->
                        <line3><xsl:value-of select="@datetaken"/></line3>                        
                        
                        <!-- Blank -->
                        <line4></line4>                        
                        
                        <!-- Your Kew Flickr Group -->
                        <attribution>Your Kew Flickr Group</attribution>
                        
                        <!--  -->
                        <type>1</type>
                        
                        <!-- Flickr Image: [url_sq] -->
                        <imageURL><xsl:value-of select="@url_sq"/></imageURL>
                        
                        <!-- Flickr geo: [latitude] -->
                        <lat><xsl:value-of select="@latitude"/></lat>
                        
                        <!-- Flickr geo: [longitude] -->
                        <lon><xsl:value-of select="@longitude"/></lon>
                        
                        <!-- Flickr View on Flickr action [http://m.flickr.com/photos/[owner]/[id]]  -->
                        <action>
                            <uri>http://m.flickr.com/photos/<xsl:value-of select="@owner"/>/<xsl:value-of select="@id"/></uri>
                            <label>View on Flickr</label>
                        </action>
                        
                    </poi>                    
                </xsl:for-each>                
            </pois>        
    </xsl:template>   
    
    
</xsl:stylesheet>
