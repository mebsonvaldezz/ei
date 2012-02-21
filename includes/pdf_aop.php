<?php

//
// Add-on print class
//
class aop
{
	var $pdf;
	var $pdf2;
	
	var $hd = array();
	
	function aop()
	{
		//
		// Static format data
		//
		$this->hd = array(
			'name' => 'EMPRESA',
			'nit' => 'NIT',
			'avenue' => 'AVENIDA O CALLE',
			'number' => 'DIRECCION',
			'zone' => 'ZONA',
			'city' => 'GUATEMALA',
			'phone' => 'TELEFONO',
			'fax' => 'FAX',
			'sig_name' => 'REPRESENTANTE LEGAL'
		);
		
		return true;
	}
	
	function import_pdf()
	{
		global $d, $d2;
		
		$this->pdf = $d;
		$this->pdf2 = $d2;
	}
	
	function export_pdf()
	{
		global $d, $d2;
		
		$d = $this->pdf;
		$d2 = $this->pdf2;
	}
	
	function explode($str)
	{
		$ary = array();
		$len = strlen($str);
		for ($i = 0; $i < $len; $i++)
		{
			$ary[] = $str{$i};
		}
		
		return $ary;
	}
	
	function header()
	{
		global $s_date, $e_date, $t_date;
		
		//
		// Place
		//
		$str = 'Guatemala';
		$this->pdf->addTextWrap(125, $this->pdf->cy(90), $this->pdf->getTextWidth(8, $str)+100, 8, $str);
		
		//
		// Today
		//
		list($t_date_day, $t_date_month, $t_date_year) = explode('-', date('d-m-Y', $t_date));
		
		list($tdd1, $tdd2) = $this->explode($t_date_day);
		list($tdm1, $tdm2) = $this->explode($t_date_month);
		list($tdy1, $tdy2, $tdy3, $tdy4) = $this->explode($t_date_year);
		
		$this->pdf->addTextWrap(50, $this->pdf->cy(106), $this->pdf->getTextWidth(10, $tdd1)+100, 10, $tdd1);
		$this->pdf->addTextWrap(70, $this->pdf->cy(106), $this->pdf->getTextWidth(10, $tdd2)+100, 10, $tdd2);
		
		$this->pdf->addTextWrap(125, $this->pdf->cy(106), $this->pdf->getTextWidth(10, $tdm1)+100, 10, $tdm1);
		$this->pdf->addTextWrap(145, $this->pdf->cy(106), $this->pdf->getTextWidth(10, $tdm2)+100, 10, $tdm2);
		
		$this->pdf->addTextWrap(200, $this->pdf->cy(106), $this->pdf->getTextWidth(10, $tdy1)+100, 10, $tdy1);
		$this->pdf->addTextWrap(220, $this->pdf->cy(106), $this->pdf->getTextWidth(10, $tdy2)+100, 10, $tdy2);
		$this->pdf->addTextWrap(245, $this->pdf->cy(106), $this->pdf->getTextWidth(10, $tdy3)+100, 10, $tdy3);
		$this->pdf->addTextWrap(264, $this->pdf->cy(106), $this->pdf->getTextWidth(10, $tdy4)+100, 10, $tdy4);
		
		//
		// NIT
		//
		$nit = $this->explode($this->hd['nit']);
		$nit_pos = array(70, 85, 100, 115, 130, 145, 160, 188);
		foreach ($nit_pos as $i => $x)
		{
			$i_nit = $nit[$i];
			$this->pdf->addTextWrap($x, $this->pdf->cy(151), $this->pdf->getTextWidth(10, $i_nit)+100, 10, $i_nit);
		}
		
		//
		// Start date
		//
		list($t_date_day, $t_date_month, $t_date_year) = explode('-', date('d-m-Y', $s_date));
		
		$this->pdf->addTextWrap(285, $this->pdf->cy(143), $this->pdf->getTextWidth(10, $t_date_day)+100, 10, $t_date_day);
		$this->pdf->addTextWrap(360, $this->pdf->cy(143), $this->pdf->getTextWidth(10, $t_date_month)+100, 10, $t_date_month);
		$this->pdf->addTextWrap(425, $this->pdf->cy(143), $this->pdf->getTextWidth(10, $t_date_year)+100, 10, $t_date_year);
		
		//
		// End date
		//
		list($t_date_day, $t_date_month, $t_date_year) = explode('-', date('d-m-Y', $e_date));
		
		$this->pdf->addTextWrap(285, $this->pdf->cy(158), $this->pdf->getTextWidth(10, $t_date_day)+100, 10, $t_date_day);
		$this->pdf->addTextWrap(360, $this->pdf->cy(158), $this->pdf->getTextWidth(10, $t_date_month)+100, 10, $t_date_month);
		$this->pdf->addTextWrap(425, $this->pdf->cy(158), $this->pdf->getTextWidth(10, $t_date_year)+100, 10, $t_date_year);
		
		//
		// Organization data
		//
		$this->pdf->addTextWrap(200, $this->pdf->cy(186), $this->pdf->getTextWidth(10, $this->hd['name'])+100, 10, $this->hd['name']);
		$this->pdf->addTextWrap(100, $this->pdf->cy(215), $this->pdf->getTextWidth(10, $this->hd['avenue'])+100, 10, $this->hd['avenue']);
		$this->pdf->addTextWrap(220, $this->pdf->cy(215), $this->pdf->getTextWidth(10, $this->hd['number'])+100, 10, $this->hd['number']);
		$this->pdf->addTextWrap(330, $this->pdf->cy(215), $this->pdf->getTextWidth(10, $this->hd['zone'])+100, 10, $this->hd['zone']);
		$this->pdf->addTextWrap(495, $this->pdf->cy(215), $this->pdf->getTextWidth(10, $this->hd['city'])+100, 10, $this->hd['city']);
		$this->pdf->addTextWrap(100, $this->pdf->cy(243), $this->pdf->getTextWidth(10, $this->hd['city'])+100, 10, $this->hd['city']);
		$this->pdf->addTextWrap(250, $this->pdf->cy(243), $this->pdf->getTextWidth(10, $this->hd['phone'])+100, 10, $this->hd['phone']);
		$this->pdf->addTextWrap(330, $this->pdf->cy(243), $this->pdf->getTextWidth(10, $this->hd['fax'])+100, 10, $this->hd['fax']);
		
		$this->hd['sig_name'] = strtoupper_tilde($this->hd['sig_name']);
		$this->hd['sig_name'] = html_entity_decode($this->hd['sig_name']);
		
		$this->pdf->addTextWrap(250, $this->pdf->cy(670), $this->pdf->getTextWidth(8, $this->hd['sig_name'])+100, 8, $this->hd['sig_name']);
		
		$this->pdf->ezStartPageNumbers(575, $this->pdf->cy(15), 7);
		
		return true;
	}
	
	function header_preview($first_page, &$print_height)
	{
		$this->pdf->line(30, $this->pdf->cy($print_height), 575, $this->pdf->cy($print_height));
		$this->pdf->line(30, $this->pdf->cy($print_height + 2), 575, $this->pdf->cy($print_height + 2));
		
		//
		// Headers
		//
		if ($first_page)
		{
			$print_height += 15;
			
			//
			// Today
			//
			global $t_date;
			$today = date('d/m/Y', $t_date);
			
			$this->pdf->addTextWrap(30, $this->pdf->cy($print_height - 2), $this->pdf->getTextWidth(8, $today)+100, 8, $today);
			
			//
			$text = 'Reporte de Verificaci�n';
			$this->pdf->addTextWrap(30 + $this->center(545, 12, $text), $this->pdf->cy($print_height), $this->pdf->getTextWidth(12, $text)+100, 12, $text);
			
			$print_height += 3;
			$this->pdf->line(30, $this->pdf->cy($print_height), 575, $this->pdf->cy($print_height));
			$print_height += 2;
			$this->pdf->line(30, $this->pdf->cy($print_height), 575, $this->pdf->cy($print_height));
		}
		
		$print_height += 8;
		
		if (!$first_page)
		{
			$print_height += 2;
		}
		
		$headers = array(
			30 => 'Exencion',
			97 => 'Fec Exe',
			186 => 'NIT',
			205 => 'Proveedor',
			360 => 'Factura',
			400 => 'Descripci�n',
			560 => 'Total'
		);
		foreach ($headers as $x => $text)
		{
			$this->text($x, $print_height, $text, 6);
		}
		
		$print_height += 3;
		$this->pdf->line(30, $this->pdf->cy($print_height), 575, $this->pdf->cy($print_height));
		$print_height += 2;
		$this->pdf->line(30, $this->pdf->cy($print_height), 575, $this->pdf->cy($print_height));
		
		//
		$print_height += 10;
	}
	
	function text($x, $y, $text, $size, $align = '')
	{
		if (!$size)
		{
			$size = 10;
		}
		
		$text = str_replace('&AMP;', '&', $text);
		
		if ($align == '')
		{
			$this->pdf->addTextWrap($x, $this->pdf->cy($y), $this->pdf->getTextWidth($size, $text)+100, $size, $text);
		}
		else
		{
			$this->pdf->addTextWrap($x, $this->pdf->cy($y), $this->pdf->getTextWidth($size, $text)+100, $size, $text, $align);
		}
		
		return true;
	}
	
	function text_desc($x, $y, $width, $text, $size)
	{
		$more = $this->pdf->addTextWrap($x, $this->pdf->cy($y), $this->pdf->getTextWidth($size, $text)+100, $size, $text);
		
		if ($more != '')
		{
			$this->text_desc($x, $y + 15, $width, $more, $size);
			die();
		}
		
		return $y;
	}
	
	function right($width, $size, $text)
	{
		return ($width - $this->pdf->getTextWidth($size, $text));
	}
	
	function center($width, $size, $text)
	{
		return (($width - $this->pdf->getTextWidth($size, $text)) / 2);
	}
	
	function nit($nit)
	{
		return true;
	}
	
	function blocks($col_width01, $col_width02, $col_text01, $col_text02, $print_height, $font_size, $left1, $left2, $line_height)
	{
		$top = $top2 = $print_height;
		
		$col_text01 = str_replace('&AMP;', '&', $col_text01);
		$col_text02 = str_replace('&AMP;', '&', $col_text02);
		
		$max1 = $this->create_lines($col_width01, $font_size, $line_height, explode(' ', $col_text01), $left1, $top);
		if ($col_width02)
		{
			$max2 = $this->create_lines($col_width02, $font_size, $line_height, explode(' ', $col_text02), $left2, $top2);
		}
		
		return max(1, $max1, $max2);
	}
	
	function create_lines($col_width, $size, $line_height, $text, $x, &$y)
	{
		$data[] = $this->words($col_width, $size, $text);
		
		foreach ($data as $item)
		{
			$linecount = 0;
			foreach ($item as $item2)
			{
				$this->pdf->addTextWrap($x, $this->pdf->cy($y), $this->pdf->getTextWidth($size, $item2['text'])+100, $size, $item2['text']);
				
				$y += $line_height;
				$linecount++;
			}
		}
		
		return $linecount;
	}
	
	function words($width, $size, $text)
	{
		$block = array();
		$inc = 0;
		
		foreach ($text as $item)
		{
			$tw = $this->pdf->getTextWidth($size, $item);
			$inc++;
			
			if ($tw <= $width)
			{
				if (isset($block[$inc - 1]['full']) && ($width - $block[$inc - 1]['full'] >= $tw))
				{
					$block[$inc - 1]['text'] .= ' ' . $item;
					$block[$inc - 1]['full'] += $tw;
					$inc--;
				}
				
				if (!isset($block[$inc]['text']))
				{
					$block[$inc] = array(
						'text' => $item,
						'full' => $tw
					);
				}
			}
		}
		
		return $block;
	}
}

?>