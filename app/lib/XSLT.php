<?php
/**
 * Slim - a micro PHP 5 framework
 *
 * @author      Chris Daley
 * @link        
 * @copyright   2012 Chris Daley
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
 
namespace Slim\Extras\Views;

/**
 * XSLTView
 *
 * XSLTView is a custom View class that renders templates using XSLT
 *
 */

class XSLT extends \Slim\View
{
	public $dom = null;
	private $dom_doc = null;
    /**
     * Render XSLT Template
     *
     * This method will output the rendered template content
     *
     * @param   string $template The path to the XSLT template, relative to the templates directory.
     * @return  void
     */
    public function render($template)
    {
		$data = $this->data['data'];
		
			if(gettype($data) == 'object') {
				if(get_class($data) == 'DOMDocument') {
					$this->dom = $data;
				}
			}
			elseif(gettype($data) == 'string') {
				if(!$this->dom->loadXML($data)) {
					$root = $this->create_dom();
					$root->appendChild($this->dom->createCDATASection($string));
				}
			} elseif(gettype($data) == 'array') {
				$root = $this->create_dom();
				$this->array_to_xml($root, $data);
			} elseif(gettype($data) == 'NULL') {
				$root = $this->create_dom();
			}
		
		if(!isset($template) || strlen($template) < 1)
			return $this->dom->saveXML();

		$xslDoc = new \DOMDocument();
		$xslDoc->load(self::getTemplatesDirectory().'/'.$template);

		$proc = new \XSLTProcessor();
		$proc->importStylesheet($xslDoc);

		return $proc->transformToXML($this->dom);
    }

	function array_to_xml($node, $data) {
		foreach($data as $key => $value) {
			if(is_array($value)) {
				if(!is_numeric($key)){
					$node = $node->appendChild($this->dom->createElement($key));
					array_to_xml($node, $value);
				}
				else{
					$node = $node->appendChild($this->dom->createElement('item'.$key));
					array_to_xml($node, $value);
				}
			}
			else {
				try {
					$node = $node->appendChild($this->dom->createElement($key, $value));
					//$node->appendChild($this->dom->createCDATASection($value));
				} catch (Exception $e) {
					$node->appendChild($this->dom->createElement('exception', $e->getMessage()));
				}
			}
		}
	}
	
	function create_dom() {
		$this->dom = new \DOMDocument();
		$root = $this->dom->appendChild($this->dom->createElement('root'));
		$root = $root->appendChild($this->dom->createElement('data'));
		return $root;
	}

}
