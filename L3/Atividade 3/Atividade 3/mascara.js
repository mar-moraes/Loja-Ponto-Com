//adiciona mascara de cep
function MascaraCep(edit_cep){
    if(mascaraInteiro(edit_cep)==false){
    event.returnValue = false;
}       
return formataCampo(edit_cep, '00.000-000', event);
}

//adiciona mascara de data
function MascaraData(edit_nascimento){
if(mascaraInteiro(edit_nascimento)==false){
    event.returnValue = false;
}       
return formataCampo(edit_nascimento, '0000-00-00', event);
}

//adiciona mascara ao CPF
function MascaraCPF(edit_cpf){
if(mascaraInteiro(edit_cpf)==false){
    event.returnValue = false;
}       
return formataCampo(edit_cpf, '000.000.000-00', event);
}

//valida CEP
function ValidaCep(edit_cep){
exp = /\d{2}\.\d{3}\-\d{3}/
if(!exp.test(edit_cep.value))
    alert('Numero de Cep Invalido!');               
}

//valida data
function ValidaData(edit_nascimento){
exp = /\d{4}\-\d{2}\-\d{2}/
if(!exp.test(edit_nascimento.value))
    alert('Data Invalida!');                        
}

//valida o CPF digitado
function ValidaCPF(Objcpf){
var cpf = Objcpf.value;
exp = /\.|\-/g
cpf = cpf.toString().replace( exp, "" ); 
var digitoDigitado = eval(cpf.charAt(9)+cpf.charAt(10));
var soma1=0, soma2=0;
var vlr =11;

for(i=0;i<9;i++){
    soma1+=eval(cpf.charAt(i)*(vlr-1));
    soma2+=eval(cpf.charAt(i)*vlr);
    vlr--;
}       
soma1 = (((soma1*10)%11)==10 ? 0:((soma1*10)%11));
soma2=(((soma2+(2*soma1))*10)%11);

var digitoGerado=(soma1*10)+soma2;
if(digitoGerado!=digitoDigitado)        
    alert('CPF Invalido!');
if(digitoGerado!=digitoDigitado)        
    focus('edit_cpf');         
}

//valida numero inteiro com mascara
function mascaraInteiro(){
if (event.keyCode < 48 || event.keyCode > 57){
    event.returnValue = false;
    return false;
}
return true;
}

//formata de forma generica os campos
function formataCampo(campo, Mascara, evento) { 
var boleanoMascara; 

var Digitato = evento.keyCode;
exp = /\-|\.|\/|\(|\)| /g
campoSoNumeros = campo.value.toString().replace( exp, "" ); 

var posicaoCampo = 0;    
var NovoValorCampo="";
var TamanhoMascara = campoSoNumeros.length;; 

if (Digitato != 8) { // backspace 
    for(i=0; i<= TamanhoMascara; i++) { 
            boleanoMascara  = ((Mascara.charAt(i) == "-") || (Mascara.charAt(i) == ".")
                                                    || (Mascara.charAt(i) == "/")) 
            boleanoMascara  = boleanoMascara || ((Mascara.charAt(i) == "(") 
                                                    || (Mascara.charAt(i) == ")") || (Mascara.charAt(i) == " ")) 
            if (boleanoMascara) { 
                    NovoValorCampo += Mascara.charAt(i); 
                      TamanhoMascara++;
            }else { 
                    NovoValorCampo += campoSoNumeros.charAt(posicaoCampo); 
                    posicaoCampo++; 
              }              
      }      
    campo.value = NovoValorCampo;
      return true; 
}else { 
    return true; 
}
}