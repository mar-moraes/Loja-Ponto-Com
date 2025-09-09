function v_cpf(campo) {
   /*
   charAt() --- posição de um determimado caracter
   substr() --- recupera uma string de de outra
   indexOf() -- posição de um determinado caracter
   
   [1]-recebe a string
   [2]-verifica se os caracteres são válidos
   [3]-verifica se há caracteres iguais e se o comprimento da string
       é diferente de 11 e de 0
   [4]-validacao do numero do CPF
   [5]-utiliza variáveis para montar a nova string com a máscara do CPF
   */
			s = new String();
			p1 = new String();
			p2 = new String();
			p3 = new String();
			p4 = new String();
			monta_cpf = new String();
			invalidChars = new String();
		
   //[1]
   s = campo.value;
   if (s.length == 0) {
      return false;
   }

   //[2]
   invalidChars = "!#$%&'()*+,/:;<=>?[\]^`{|}~€‚ƒ„…†‡ˆ‰Š‹Œ‘’“”•–—˜™š›œŸ ¡¢£¤¥¦§¨©ª«¬­®¯°±²³´µ¶·¸¹º»¼½¾¿abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏĞÑÒÓÔÕÖ×ØÙÚÛÜİŞßàáâãäåæçèéêëìíîïğñòóôõö÷øùúûüışÿ";
   
			for (i=0;i<invalidChars.length;i++) {
			   bChar = invalidChars.charAt(i);
			   if (s.indexOf(bChar,0) > -1) {
			      alert('Você digitou um caracter inválido');
			      campo.focus();
			      return false;
			   }
			}
			//[3]
			if ((s.length != 11 && s.length != 0) || s == '00000000000' || s == '11111111111' || s == '22222222222' || s == '33333333333' || s == '44444444444' || s == '55555555555' || s == '66666666666' || s == '77777777777' || s == '88888888888' || s == '99999999999') {
			   alert('Quantidade ou conjunto de caracteres inválido.');
			   campo.focus();
			   return false;
			}
   //[4]
   ok_CPF(campo);
   //[5]
   p1 = s.substr(0,3);
   p2 = s.substr(3,3);
   p3 = s.substr(6,3);
   p4 = s.substr(9,2);
   monta_cpf = p1 + '.' + p2 + '.' + p3 + '-' + p4;
   if (monta_cpf != '..-') {
						campo.value = '';
						campo.value = monta_cpf;
   }

			return true;
}

function modo_editar_CPF(campo) {
   c = new String();
   
   c = campo.value;
			campo.value = c.replace('.','');
			c = campo.value;
			campo.value = c.replace('.','');
			c = campo.value;
			campo.value = c.replace('-','');
			c = campo.value;		
}

function ok_CPF(campo) {
   a = new String();
   a = campo.value;
   
   var i = 0;
   var num = '';
   var multi = 0;
   var cont1 = 10;
   var cont2 = 11;
   var soma = 0;
   var d1, d2, resto1, resto2;
   
   //[INICIO] PRIMEIRO DIGITO
   for (i=0;i<9;i++) {
      num = a.charAt(i);
      multi = cont1 * num;
      soma = soma + multi;
      cont1 = cont1 - 1;
      num = '';
   }
   
   resto1 = soma % 11;
   if (resto1 == 0 || resto1 == 1) {
      d1 = 0;
   }
   else {
      d1 = 11 - resto1;
   }
   //[FIM] PRIMEIRO DIGITO

   //[INICIO] SEGUNDO DIGITO
   soma = 0;
   multi = 0;
   for (i=0;i<10;i++) {
      num = a.charAt(i);
      multi = cont2 * num;
      soma = soma + multi;
      cont2 = cont2 - 1;
      num = '';
   }
   
   resto2 = soma % 11;
   if (resto2 == 0 || resto2 == 1) {
      d2 = 0;
   }
   else {
      d2 = 11 - resto2;
   }   
   //[FIM] SEGUNDO DIGITO
   
   if (a.charAt(9) != d1 || a.charAt(10) != d2) {
      alert('O digito está errado');
      campo.focus();
      return false;
   }
   
   return true;
}