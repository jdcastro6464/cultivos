import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split
from sklearn.linear_model import LinearRegression
from flask import Flask, render_template, request, jsonify

app = Flask(__name__)

application = app

# Configuaración de pantalla see
desired_width = 320
pd.set_option('display.width', desired_width)
pd.set_option('display.max_columns', 16)


def num(s):
	try:
		return int(s)
	except ValueError:
		return float(s)


@app.route('/prediccion_variable',methods=['POST'])
def funcion_predictora():
	idvariable = num(request.form['idVariable'])

	#url = "https://colomboalemanbq.com/cultivo/tmp/plantilla_prediccion.csv"
	#url = "../public_html/cultivo/tmp/plantilla_prediccion.csv"
	url = "C:\\xampp\\htdocs\\cultivos\\tmp\\plantilla_prediccion.csv"
	datos = pd.read_csv(url, sep=";", low_memory=False)

	datos['HUMEDAD'] = datos['HUMEDAD'].str.replace(',', '.').astype(float)
	datos['LUMINOSIDAD'] = datos['LUMINOSIDAD'].str.replace(',', '.').astype(float)
	try:
		datos['NITROGENO'] = datos['NITROGENO'].str.replace(',', '.').astype(float)
	except:
		datos['NITROGENO'] = datos['NITROGENO'].astype(float)
	try:
		datos['POTASIO'] = datos['POTASIO'].str.replace(',', '.').astype(float)
	except:
		datos['POTASIO'] = datos['POTASIO'].astype(float)
	datos['FOSFORO'] = datos['FOSFORO'].str.replace(',', '.').astype(float)
	datos['ACIDEZ'] = datos['ACIDEZ'].str.replace(',', '.').astype(float)
	datos['TEMP'] = datos['TEMP'].str.replace(',', '.').astype(float)

	datos = datos.drop(columns=['ID'])

	if idvariable == 17:
		X_train, X_test, y_train, y_test = train_test_split(datos.drop('HUMEDAD', axis = 'columns'),datos['HUMEDAD'],
			train_size = 0.8,random_state = 1234,shuffle = True)
	elif idvariable == 18:
		X_train, X_test, y_train, y_test = train_test_split(datos.drop('LUMINOSIDAD', axis='columns'), datos['LUMINOSIDAD'],
			train_size=0.8, random_state=1234, shuffle=True)
	elif idvariable == 19:
		X_train, X_test, y_train, y_test = train_test_split(datos.drop('NITROGENO', axis='columns'), datos['NITROGENO'],
			train_size=0.8, random_state=1234, shuffle=True)
	elif idvariable == 20:
		X_train, X_test, y_train, y_test = train_test_split(datos.drop('POTASIO', axis='columns'), datos['POTASIO'],
			train_size=0.8, random_state=1234, shuffle=True)
	elif idvariable == 21:
		X_train, X_test, y_train, y_test = train_test_split(datos.drop('FOSFORO', axis='columns'), datos['FOSFORO'],
			train_size=0.8, random_state=1234, shuffle=True)
	elif idvariable == 22:
		X_train, X_test, y_train, y_test = train_test_split(datos.drop('ACIDEZ', axis='columns'), datos['ACIDEZ'],
			train_size=0.8, random_state=1234, shuffle=True)

	modelo = LinearRegression()
	modelo.fit(X_train, y_train)

	predicciones = modelo.predict(X_test)

	try:
		val_humedad = num(request.form['val_humedad'])
	except KeyError:
		val_humedad = 0

	try:
		val_luminosidad = num(request.form['val_luminosidad'])
	except KeyError:
		val_luminosidad = 0

	try:
		val_nitrogeno = num(request.form['val_nitrogeno'])
	except KeyError:
		val_nitrogeno = 0

	try:
		val_potasio = num(request.form['val_potasio'])
	except KeyError:
		val_potasio = 0

	try:
		val_fosforo = num(request.form['val_fosforo'])
	except KeyError:
		val_fosforo = 0

	try:
		val_acidez = num(request.form['val_acidez'])
	except KeyError:
		val_acidez = 0

	try:
		val_temp = num(request.form['val_temp'])
	except KeyError:
		val_temp = 0

	if idvariable == 17:
		nueva_info = pd.DataFrame(np.array([[val_luminosidad, val_nitrogeno, val_potasio, val_fosforo, val_acidez,
			val_temp]]), columns=['LUMINOSIDAD', 'NITROGENO',
		'POTASIO', 'FOSFORO', 'ACIDEZ',
		'TEMP'])
	elif idvariable == 18:
		nueva_info = pd.DataFrame(np.array([[val_humedad, val_nitrogeno, val_potasio, val_fosforo, val_acidez,
			val_temp]]), columns=['HUMEDAD', 'NITROGENO', 'POTASIO',
		'FOSFORO', 'ACIDEZ', 'TEMP'])
	elif idvariable == 19:
		nueva_info = pd.DataFrame(np.array([[val_humedad, val_luminosidad, val_potasio, val_fosforo, val_acidez,
			val_temp]]), columns=['HUMEDAD', 'LUMINOSIDAD', 'POTASIO',
		'FOSFORO', 'ACIDEZ', 'TEMP'])
	elif idvariable == 20:
		nueva_info = pd.DataFrame(np.array([[val_humedad, val_luminosidad, val_nitrogeno, val_fosforo, val_acidez,
			val_temp]]), columns=['HUMEDAD', 'LUMINOSIDAD',
		'NITROGENO', 'FOSFORO', 'ACIDEZ',
		'TEMP'])
	elif idvariable == 21:
		nueva_info = pd.DataFrame(np.array([[val_humedad, val_luminosidad, val_nitrogeno, val_potasio, val_acidez,
			val_temp]]), columns=['HUMEDAD', 'LUMINOSIDAD',
		'NITROGENO', 'POTASIO', 'ACIDEZ',
		'TEMP'])
	elif idvariable == 22:
		nueva_info = pd.DataFrame(np.array([[val_humedad, val_luminosidad, val_nitrogeno, val_potasio, val_fosforo,
			val_temp]]), columns=['HUMEDAD', 'LUMINOSIDAD',
		'NITROGENO', 'POTASIO', 'FOSFORO',
		'TEMP'])

	predic = modelo.predict(nueva_info)

	response = {
		"prediccion_value": predic.tolist()
	}

	return jsonify(response)


if __name__ == '__main__':
	app.debug = True
	app.run()